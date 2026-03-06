<?php

namespace App\Filament\Resources\InterviewBatchResource\Pages;

use App\Filament\Resources\InterviewBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInterviewBatch extends CreateRecord
{
    protected static string $resource = InterviewBatchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
