<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterviewBatchResource\Pages;
use App\Filament\Resources\InterviewBatchResource\RelationManagers;
use App\Models\InterviewBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterviewBatchResource extends Resource
{
    protected static ?string $model = InterviewBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\TextInput::make('interview_batch_code')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('interview_batch_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('interview_date')
                    ->required(),

                Forms\Components\TimePicker::make('start_time')
                    ->required(),

                Forms\Components\TimePicker::make('end_time')
                    ->required(),

                Forms\Components\TextInput::make('interview_location')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('batch_size')
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('capacity_status')
                    ->options([
                        'open' => 'Open',
                        'full' => 'Full',
                    ])
                    ->default('open')
                    ->required(),

                Forms\Components\Select::make('workflow_status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('scheduled')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('interview_batch_code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('interview_batch_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('interview_date')
                    ->date(),

                Tables\Columns\TextColumn::make('start_time'),

                Tables\Columns\TextColumn::make('batch_size'),

                Tables\Columns\BadgeColumn::make('capacity_status')
                    ->colors([
                        'success' => 'open',
                        'danger' => 'full',
                    ]),

                Tables\Columns\BadgeColumn::make('workflow_status'),
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
            'index' => Pages\ListInterviewBatches::route('/'),
            'create' => Pages\CreateInterviewBatch::route('/create'),
            'edit' => Pages\EditInterviewBatch::route('/{record}/edit'),
        ];
    }
}
