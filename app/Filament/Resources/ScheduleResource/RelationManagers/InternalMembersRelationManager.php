<?php

namespace App\Filament\Resources\ScheduleResource\RelationManagers;

use App\Models\InternalMember;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class InternalMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'internalMembers';
    protected static ?string $title = 'Member Internal';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Pilih Member Internal')
                    ->validationAttribute('Member Internal')
                    ->multiple()
                    ->searchable()
                    ->columnSpanFull()
                    ->required()
                    ->options(function () {
                        return User::query()
                            ->selectRaw('CONCAT(name, " - ", email) AS mapping, id, name, email')
                            ->where('role_id', Role::USER)
                            ->whereNot('id', Auth::user()->id)
                            ->whereDoesntHave('internalMembers', function ($query) {
                                return $query->where('schedule_id', $this->ownerRecord->id);
                            })
                            ->pluck('mapping', 'id');
                    }),
                // ToggleButtons::make('status')
                //     ->label('Status Kehadiran')
                //     ->required()
                //     ->grouped()
                //     ->live()
                //     ->options([
                //         'hadir' => 'Hadir',
                //         'absen' => 'Absen',
                //     ])
                //     ->icons([
                //         'hadir' => 'heroicon-o-check',
                //         'absen' => 'heroicon-o-x-mark',
                //     ])
                //     ->colors([
                //         'hadir' => 'success',
                //         'absen' => 'danger',
                //     ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with([
                    'user'
                ]);
            })
            ->modelLabel('Member Internal')
            ->pluralModelLabel('Member Internal')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Alasan')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->placeholder('Tidak diisi'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Kehadiran')
                    ->placeholder('Belum memutuskan')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'hadir' => 'success',
                            'absen' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'hadir' => 'Hadir',
                            'absen' => 'Tidak Hadir',
                            default => 'Belum Memutuskan',
                        };
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function ($data) {
                        $model = new InternalMember();
                        foreach ($data['user_id'] ?? [] as $userId) {
                            $internalMember = new InternalMember();
                            $internalMember->user_id = $userId;
                            $internalMember->schedule_id = $this->ownerRecord->id;
                            $internalMember->save();

                            $model = $internalMember;
                        }

                        return $model;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
