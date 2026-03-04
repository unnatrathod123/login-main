<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //

                // // For Assigning Task to Single Intern
                // Forms\Components\Select::make('intern_id')
                //     ->label('Assign Intern')
                //     ->relationship('intern', 'name')
                //     ->options(
                //         \App\Models\User::where('role', 'intern')
                //             ->pluck('name', 'id')
                //     )
                //     ->searchable()
                //     ->required(),
                // //-----------------------------------------------

                // For Assigning Task to Multiple Interns
                Forms\Components\Select::make('interns')
                    ->label('Assign Interns')
                    ->multiple()
                    ->relationship('interns', 'name')
                    ->options(
                        \App\Models\User::where('role', 'intern')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                //-----------------------------------------------

                Forms\Components\TextInput::make('title')->required(),

                Forms\Components\RichEditor::make('description')->required(),

                Forms\Components\DatePicker::make('deadline')->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'assigned' => 'Assigned',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('assigned'),

                Forms\Components\FileUpload::make('submission_file')
                    ->directory('task_submissions')
                    ->disk('public'),

                Forms\Components\Textarea::make('feedback')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                //
                 Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('intern_id'),
                Tables\Columns\TextColumn::make('deadline'),
                Tables\Columns\TextColumn::make('status'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
