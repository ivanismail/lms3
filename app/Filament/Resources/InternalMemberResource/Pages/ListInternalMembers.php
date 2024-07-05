<?php

namespace App\Filament\Resources\InternalMemberResource\Pages;

use App\Filament\Resources\InternalMemberResource;
use App\Models\InternalMember;
use App\Models\Role;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListInternalMembers extends ListRecords
{
    protected static string $resource = InternalMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function ($data) {
                    $user = new User();
                    $user->name = $data['name'];
                    $user->email = $data['email'];
                    $user->password = Hash::make($data['password']);
                    $user->role_id = Role::USER;
                    $user->tag_id = $data['tag_id'];
                    $user->save();

                    return $user;
                }),
        ];
    }
}
