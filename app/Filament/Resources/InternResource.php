<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternResource\Pages;
use App\Filament\Resources\InternResource\RelationManagers;
use App\Models\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InternResource extends Resource
{
    protected static ?string $model = Intern::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('intern_id')
                    ->label('Intern ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('application_id')
                    ->label('Application ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('application.name')
                    ->label('Intern Name')
                    ->description(fn ($record) => $record->application->college)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('application.degree')
                    ->label('Intern Course')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('application.domain')
                    ->label('Intern Domain')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('internship_duration')
                    ->label('Internship Duration')
                    ->getStateUsing(function ($record) {

                        if (!$record->application) 
                        {
                            return 'No Application';
                        }

                        return $record->application->duration . ' ' . $record->application->duration_unit . '';
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'active',
                        //'warning' => 'active',
                        'success' => 'completed',
                        'danger' => 'dropped',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
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
            'index' => Pages\ListInterns::route('/'),
            'create' => Pages\CreateIntern::route('/create'),
            'edit' => Pages\EditIntern::route('/{record}/edit'),
        ];
    }
}
