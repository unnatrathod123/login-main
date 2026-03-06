<?php

namespace App\Filament\Resources\InterviewBatchResource\Pages;

use App\Filament\Resources\InterviewBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInterviewBatch extends EditRecord
{
    protected static string $resource = InterviewBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
