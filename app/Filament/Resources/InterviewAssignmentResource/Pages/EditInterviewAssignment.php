<?php

namespace App\Filament\Resources\InterviewAssignmentResource\Pages;

use App\Filament\Resources\InterviewAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInterviewAssignment extends EditRecord
{
    protected static string $resource = InterviewAssignmentResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
