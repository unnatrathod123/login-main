<?php

namespace App\Filament\Resources\InterviewBatchResource\Pages;

use App\Filament\Resources\InterviewBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInterviewBatches extends ListRecords
{
    protected static string $resource = InterviewBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
