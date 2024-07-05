<?php

namespace App\Filament\Resources\ScheduleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExternalMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'externalMembers';
    protected static ?string $title = 'Member External';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Member External')
            ->pluralModelLabel('Member External')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(
                        isIndividual:true,
                    ),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(
                        isIndividual:true,
                    ),
                Tables\Columns\TextColumn::make('description')
                    ->label('Alasan')
                    ->sortable()
                    ->searchable(
                        isIndividual:true,
                    )
                    ->placeholder('Tidak diisi'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Kehadiran')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'hadir' => 'success',
                            'absen' => 'danger',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'hadir' => 'Hadir',
                            'absen' => 'Tidak Hadir',
                        };
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
