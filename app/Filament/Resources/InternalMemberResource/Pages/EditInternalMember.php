<?php

namespace App\Filament\Resources\InternalMemberResource\Pages;

use App\Filament\Resources\InternalMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalMember extends EditRecord
{
    protected static string $resource = InternalMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
