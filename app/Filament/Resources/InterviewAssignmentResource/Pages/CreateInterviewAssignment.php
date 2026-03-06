<?php

namespace App\Filament\Resources\InterviewAssignmentResource\Pages;

use App\Filament\Resources\InterviewAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInterviewAssignment extends CreateRecord
{
    protected static string $resource = InterviewAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
