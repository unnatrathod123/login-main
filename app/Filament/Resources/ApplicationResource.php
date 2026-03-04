<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\InterviewBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Dropdown;
//use App\Filament\Resources\InterviewBatchResource;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
//use Filament\Infolists\Components\Section;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use App\Mail\InterviewScheduledMail;
use Illuminate\Support\Facades\Mail;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use App\Models\Applicant;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Filament\Support\Enums\Alignment;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    //protected static ?string $model = Applicant::class;
   
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'Name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make('Intern Details')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->maxLength(15),

                        TextInput::make('college')
                            ->label('College Name')
                            ->maxLength(255),

                        TextInput::make('degree')
                            ->required()
                            ->maxLength(100),

                        // --- ADD THESE TWO FIELDS HERE ---
                        TextInput::make('last_exam_appeared')
                            ->label('Last Exam Appeared')
                            ->placeholder('e.g. HSC, Sem 6')
                            ->maxLength(255),

                        TextInput::make('cgpa')
                            ->label('CGPA / Percentage')
                            ->numeric()       // Ensures only numbers are entered
                            ->step(0.01)      // Allows decimals like 8.55
                            ->maxValue(100),  // Optional: prevents unrealistic numbers
                        // ---------------------------------

                        TextInput::make('domain')
                            ->label('Internship Domain')
                            ->required()
                            //->searchable()
                          //  ->disabled(fn ($record) => $record?->status !== 'applied'),
                    ]),
                ]),

                    Section::make('Skills')
                        ->schema([
                            Textarea::make('skills')
                                ->label('Skills (comma separated)')
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
                    ->required()
                    ->visible(fn () => auth()->user()?->role === 'admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordClasses(fn ($record) => $record->status === 'interview_scheduled' ? 'bg-green-50 border-l-4 border-green-500' : null)
            ->poll('5s') // ⬅ auto refresh
            ->defaultSort('created_at', 'desc') // 🔥 newest on top
            ->columns([
                // To display in table
                 //TextColumn::make('user_id'),
                TextColumn::make('name')
                    ->searchable()
                    // 1. Change Font Color to Green ('success') if scheduled
                    ->color(fn ($record) => $record->status === 'interview_scheduled' ? 'success' : null)
                    // 2. Add a Checkmark Icon if scheduled
                    ->icon(fn ($record) => $record->status === 'interview_scheduled' ? 'heroicon-m-check-badge' : null)
                    // 3. Make the name bold if scheduled
                    ->weight(fn ($record) => $record->status === 'interview_scheduled' ? 'bold' : 'normal'),
                
                TextColumn::make('application_id'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('email_verified_at')->searchable(),
                TextColumn::make('phone') ->toggleable(),
                TextColumn::make('duration'.'duration_unit')
                ->label('Duration'),
                BadgeColumn::make('status')
                ->colors([
                    'primary' => 'applied',
                    'warning' => 'shortlisted',
                    'success' => 'interview_scheduled',
                    'danger' => 'rejected',
                ])->alignCenter()
                ->formatStateUsing(fn (string $state) => ucfirst($state)),
                
                TextColumn::make('college') ->toggleable(),
                TextColumn::make('degree') ->toggleable(),
                // --- ADD THESE TWO COLUMNS HERE ---
                TextColumn::make('last_exam_appeared')
                    ->label('Last Exam')
                    ->toggleable(),

                TextColumn::make('cgpa')
                    ->label('CGPA')
                    ->sortable()
                    ->toggleable(),
                // ----------------------------------
                TextColumn::make('domain') ->toggleable(),
                TextColumn::make('skills')  ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resume_path')
                    ->label('Resume')
                    ->formatStateUsing(fn () => 'View Resume')
                    ->url(fn ($record) => asset('storage/' . $record->resume_path))
                    ->openUrlInNewTab()
                    //->downloadable()
                    ->sortable(false),
                TextColumn::make('title')
                    ->label('Interview Batch')
                    ->sortable(),
                TextColumn::make('created_at')->dateTime(),
                
            ])

        // filter
            ->filters([

             //to filter from status

                SelectFilter::make('status')
                ->options(Application::statuses()),
                ])
                    
                ->actions([

                    // // TO view
                     ViewAction::make()
                        ->label('') // remove text
                        ->tooltip('View Application'),
                    
                    // for edit 
                    Tables\Actions\EditAction::make()
                        ->label('')
                        ->tooltip('Edit Application'),


                    Action::make('download')
                        ->label('')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->tooltip('Download Resume')
                        ->url(fn ($record) => asset('storage/' . $record->resume_path))
                        ->openUrlInNewTab(true),
                    
                     // for change status from intern
                        // Action::make('Schedule Interview')
                        //     ->visible(fn ($record) => $record->status === 'applied')
                        //     ->action(fn ($record) => $record->update(['status' => 'interviewed']))
                        //     ->color('warning'),

                        // Action::make('Select Intern')
                        //     ->visible(fn ($record) => $record->status === 'interviewed')
                        //     ->action(fn ($record) => $record->update(['status' => 'selected']))
                        //     ->color('success'),


                // Button For User creation 
                Action::make('createUser')
                    ->label('Create User')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->tooltip('Create Intern User')

                    // ✅ show only when shortlisted
                    ->visible(fn ($record) =>
                        $record->status === 'shortlisted'
                        && $record->user_id === null
                    )

                    ->action(function ($record) {

                        // $password = Str::random(8);

                        $user = User::create([
                            'name' => $record->name,
                            'email' => $record->email,
                            'password' => Hash::make('password123'),
                            'email_verified_at' => $record->email_verified_at,
                            'role' => 'intern',
                        ]);

                        $record->update([
                            'user_id' => $user->id
                        ]);

                        Notification::make()
                            ->title('Intern account created')
                            ->body("Temporary Password:password123")
                            ->success()
                            ->send();
                    }),

                ])


                ->actionsAlignment('left') // for aligning buttons
                ->actionsColumnLabel('Actions')


                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([


                        //---- To update status to applied for testing purpose 

                        BulkAction::make('markAsApplied')
                            ->label('Move back to Applied')
                            ->icon('heroicon-o-arrow-uturn-left')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->action(function (Collection $records) {

                                $records->each(function ($application) {

                                    // Only update if currently interview_scheduled
                                    if ($application->status === 'interview_scheduled') {
                                        $application->update([
                                            'status' => 'applied',
                                            'interview_batch_id' => null, // optional
                                        ]);
                                    }
                                });
                            }),

                            
                        // --- To delete records ----------
                        Tables\Actions\DeleteBulkAction::make(),

                        
                        // --- NEW SMART SCHEDULE ACTION ---
                        BulkAction::make('schedule_interview')
                            ->label('Schedule Interview')
                            ->icon('heroicon-o-calendar-days')
                            ->requiresConfirmation()
                            ->color('success')
                            ->form([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Interview Date')
                                    ->required()
                                    ->minDate(now()),
                                
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Start Time (First Batch)')
                                    ->required()
                                    ->default('10:00:00'),

                                Forms\Components\TextInput::make('location')
                                    ->label('Offline Location')
                                    ->required()
                                    ->placeholder('e.g., Conf Room A, 2nd Floor, Tech Park'),
                                
                                Forms\Components\TextInput::make('batch_size')
                                    ->label('Candidates per Batch')
                                    ->numeric()
                                    ->default(10) // Default is 10 as per your requirement
                                    ->required(),

                                Forms\Components\TextInput::make('duration_per_batch')
                                    ->label('Duration per Batch (Minutes)')
                                    ->numeric()
                                    ->default(60) // Each batch takes 1 hour
                                    ->required(),
                            ])
                            ->action(function (Collection $records, array $data) {
                                   
    // 1️⃣ FILTER candidates (status-based)
    $eligibleRecords = $records->reject(fn ($record) =>
        in_array($record->status, ['selected', 'rejected', 'interview_scheduled'])
    );

    if ($eligibleRecords->isEmpty()) {
        Notification::make()
            ->title('Operation Cancelled')
            ->body('All selected candidates have already been processed.')
            ->warning()
            ->send();
        return;
    }

    // 2️⃣ RANDOMIZE
    $shuffledRecords = $eligibleRecords->shuffle();

    // 3️⃣ CHUNK INTO BATCHES
    $chunks = $shuffledRecords->chunk($data['batch_size']);
    $currentBatchTime = \Carbon\Carbon::parse($data['start_time']);
    $batchCount = 1;

    foreach ($chunks as $chunk) {

        // Create Interview Batch
        $batch = \App\Models\InterviewBatch::create([
            'batch_name'     => "Batch {$batchCount} ({$chunk->count()} Candidates)",
            'interview_date' => $data['start_date'],
            'interview_time' => $currentBatchTime->format('H:i:s'),
            'location'       => $data['location'],
        ]);

        foreach ($chunk as $applicant) {

            // 🔒 HARD BLOCK: Email must be verified
            if (!$applicant->email_verified_at) {
                continue; // skip silently OR log if you want
            }

            try {
                // ✉️ Send interview email FIRST
                Mail::to($applicant->email)
                    ->send(new InterviewScheduledMail($applicant, $batch));

                // ✅ Update status ONLY after email success
                $applicant->update([
                    'interview_batch_id' => $batch->id,
                    'status'             => 'interview_scheduled',
                ]);

            } catch (\Throwable $e) {

                // ❌ Email failed → do NOT update status
                \Log::error('Interview email failed', [
                    'application_id' => $applicant->id,
                    'email' => $applicant->email,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }
        }

        $currentBatchTime->addMinutes($data['duration_per_batch']);
        $batchCount++;
    }

    Notification::make()
        ->title('Scheduling Completed')
        ->body('Only verified applicants with successful emails were scheduled.')
        ->success()
        ->send();
                                })
                        // ---------------------------------
                    ]),
                ]);
                               
    }
            // Forms\Components\Select::make('mode')
            //     ->options([
            //         'Online' => 'Online',
            //         'Offline' => 'Offline',
            //     ])
            //     ->reactive()
            //     ->required(),

            // Forms\Components\TextInput::make('meeting_link')
            //     ->visible(fn ($get) => $get('mode') === 'Online'),

            // Forms\Components\TextInput::make('location')
            //     ->visible(fn ($get) => $get('mode') === 'Offline'),

    

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

       


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
            // 'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }

    // For Not displaying where Values are NULL
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('name')
            ->whereNotNull('phone')
            ->whereNotNull('college');
    }
}