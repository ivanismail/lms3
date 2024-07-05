<?php

namespace App\Filament\Resources\ScheduleResource\RelationManagers;

use App\Utils\Utils;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotulensRelationManager extends RelationManager
{
    protected static string $relationship = 'notulens';
    protected static ?string $title = 'Notulen';

    public function isReadOnly(): bool
    {
        if(Utils::isUser()) return true;

        if (blank($this->getPageClass())) {
            return false;
        }

        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return false;
        }

        if (! $panel->hasReadOnlyRelationManagersOnResourceViewPagesByDefault()) {
            return false;
        }

        return is_subclass_of($this->getPageClass(), ViewRecord::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('content')
                    ->label('Notulen')
                    ->placeholder('Masukkan notulen meeting')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Member External')
            ->pluralModelLabel('Member External')
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label('Notulen')
                    ->sortable()
                    ->searchable(),
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
