<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\InterviewBatch;
use App\Models\Intern;

use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

use Filament\Forms;
use Filament\Tables;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Filament\Support\Enums\Alignment;

use App\Mail\InterviewScheduledMail;
use Carbon\Carbon;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Intern Details')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->required(),

                        TextInput::make('college'),

                        TextInput::make('degree')
                            ->required(),

                        TextInput::make('last_exam_appeared')
                            ->label('Last Exam Appeared'),

                        TextInput::make('cgpa')
                            ->label('CGPA')
                            ->numeric(),

                        TextInput::make('domain')
                            ->required(),

                    ]),
                ]),

            Section::make('Skills')
                ->schema([
                    Textarea::make('skills')
                        ->rows(3)
                        ->required(),
                ]),

            Section::make('Resume')
                ->schema([
                    FileUpload::make('resume_path')
                        ->label('Resume')
                        ->disk('public')
                        ->directory('resumes')
                        ->acceptedFileTypes([
                            'application/pdf',
                        ])
                        ->downloadable()
                        ->openable()
                        ->preserveFilenames(),
                ]),

            Select::make('status')
                ->options(Application::statuses())
                ->default(Application::STATUS_APPLIED)
                ->required(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->recordUrl(null)

            ->defaultSort('created_at', 'desc')

            ->poll('5s')

            ->columns([

                TextColumn::make('name')
                    ->searchable()
                    ->color(fn ($record) => $record->status === 'interview_scheduled' ? 'success' : null)
                    ->icon(fn ($record) => $record->status === 'interview_scheduled' ? 'heroicon-m-check-badge' : null)
                    ->weight(fn ($record) => $record->status === 'interview_scheduled' ? 'bold' : 'normal'),

                TextColumn::make('application_id'),

                TextColumn::make('email')->searchable(),

                TextColumn::make('phone')->toggleable(),

                TextColumn::make('duration')
                    ->label('Duration'),

                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'applied',
                        'warning' => 'shortlisted',
                        'success' => 'interview_scheduled',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('college')->toggleable(),

                TextColumn::make('degree')->toggleable(),

                TextColumn::make('last_exam_appeared')
                    ->label('Last Exam')
                    ->toggleable(),

                TextColumn::make('cgpa')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('domain')->toggleable(),

                TextColumn::make('skills')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('resume_path')
                    ->label('Resume')
                    ->formatStateUsing(fn () => 'View Resume')
                    ->url(fn ($record) => asset('storage/' . $record->resume_path))
                    ->openUrlInNewTab(),

                TextColumn::make('created_at')
                    ->dateTime(),

            ])

            ->filters([
                SelectFilter::make('status')
                    ->options(Application::statuses()),
            ])

            ->actions([
     Tables\Actions\ActionGroup::make(
                    [
                            ViewAction::make()
                                ->label('')
                                ->tooltip('View Application'),

                            Tables\Actions\EditAction::make()
                                ->label('')
                                ->tooltip('Edit Application'),

                            Action::make('download')
                                ->label('')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->tooltip('Download Resume')
                                ->url(fn ($record) => asset('storage/' . $record->resume_path))
                                ->openUrlInNewTab(),

                        // To create intern for whom status is shortlisted
                        Action::make('createIntern')
                                ->label('')
                                ->icon('heroicon-o-user-plus')
                                ->color('success')
                                ->tooltip('Create Intern Account')

                                ->visible(fn ($record) =>
                                    $record->status === 'shortlisted'
                                    && $record->intern_id === null
                                )

                                ->action(function ($record) {

                                    $result = Intern::createFromApplication($record);

                                    Notification::make()
                                        ->title('Intern Account Created')
                                        ->body("Temporary Password: {$result['password']}")
                                        ->success()
                                        ->send();
                                })

                                ->after(function () {
                                    $this->dispatch('refresh');
                                }),
                        ])
                        
                    ])

           
           ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->actionsColumnLabel('Actions')

            ->bulkActions(
            [

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('scheduleInterview')
                ->label('Schedule Interview')
                ->icon('heroicon-o-calendar')
                ->form([
                    Forms\Components\Select::make('interview_batch_id')
                        ->label('Select Interview Batch')
                        ->options(
                            \App\Models\InterviewBatch::where('capacity_status', 'open')
                                ->pluck('interview_batch_name', 'id')
                        )
                        ->required()
                ])
                ->requiresConfirmation()
                ->action(function ($records, $data) {

                    $batch = \App\Models\InterviewBatch::find($data['interview_batch_id']);

                    if (!$batch) {
                        return;
                    }

                    $currentCount = $batch->assignments()->count();
                    $remainingSlots = $batch->batch_size - $currentCount;

                    if ($remainingSlots <= 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Batch is already FULL')
                            ->danger()
                            ->send();
                        return;
                    }

                    $scheduledCount = 0;

                    foreach ($records as $record) {

                        if ($scheduledCount >= $remainingSlots) {
                            break;
                        }

                        // Prevent duplicate scheduling
                        $exists = \App\Models\InterviewAssignment::where('application_id', $record->id)
                            ->where('interview_batch_id', $batch->id)
                            ->exists();

                        if (!$exists) {

                            \App\Models\InterviewAssignment::create([
                                'application_id' => $record->id,
                                'interview_batch_id' => $batch->id,
                                // 'assignment_code' => $code,
                            ]);
                            
                            $record->update([
                                'status' => 'interview_scheduled'
                            ]);
                            // Mail::to($record->email)
                            //     ->send(new InterviewScheduledMail(
                            //         $batch,      // first parameter
                            //         $record      // second parameter
                            //     ));
                            $scheduledCount++;
                        }
                    }

                    // Auto mark batch FULL
                    if ($batch->assignments()->count() >= $batch->batch_size) {
                        $batch->update([
                            'capacity_status' => 'full'
                        ]);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title("{$scheduledCount} Applicants Scheduled Successfully")
                        ->success()
                        ->send();

                })
                        
                
                        ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('name')
            ->whereNotNull('phone')
            ->whereNotNull('college');
    }
}