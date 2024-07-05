<?php

namespace App\Livewire;

use App\Models\ExternalMember;
use App\Models\Schedule;
use App\Utils\Utils;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset as ComponentsFieldset;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login;
use Filament\Pages\BasePage;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Livewire\Attributes\Title;
use Livewire\Component;

class PresensiPage extends SimplePage
{

    public ?array $data = [];
    public Schedule $schedule;

    protected static string $view = 'livewire.presensi-page';
    protected static ?string $title = 'Kehadiran Meeting';

    public function mount($code = '')
    {
        $this->schedule = Schedule::query()
            ->with('room')
            ->where('code', $code)
            ->firstOrFail();
    }
    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [];
    }

    public function getMaxWidth(): MaxWidth | string | null
    {
        return MaxWidth::TwoExtraLarge;
    }

    public function hasTopbar(): bool
    {
        return false;
    }

    function create()
    {
        $data = $this->form->getState();

        $externalMember = new ExternalMember();
        $externalMember->name = $data['name'];
        $externalMember->email = $data['email'];
        $externalMember->description = $data['description'] ?? '';
        $externalMember->status = $data['status'];
        $externalMember->schedule_id = $this->schedule->id;
        $externalMember->save();

        $this->form->fill();

        Notification::make()
            ->title('Berhasil!')
            ->body('Berhasil melakukan konfirmasi kehadiran meeting')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Diri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->placeholder('Masukkan nama')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Masukkan email')
                            ->email()
                            ->required()
                            ->unique(
                                table: 'external_members',
                                column: 'email',
                                modifyRuleUsing: function ($rule) {
                                    return $rule->where('schedule_id', $this->schedule->id);
                                }
                            ),
                        ToggleButtons::make('status')
                            ->label('Status Kehadiran')
                            ->required()
                            ->grouped()
                            ->live()
                            ->options([
                                'hadir' => 'Hadir',
                                'absen' => 'Absen',
                            ])
                            ->icons([
                                'hadir' => 'heroicon-o-check',
                                'absen' => 'heroicon-o-x-mark',
                            ])
                            ->colors([
                                'hadir' => 'success',
                                'absen' => 'danger',
                            ]),
                        Textarea::make('description')
                            ->label('Alasan')
                            ->validationAttribute('Alasan')
                            ->columnSpanFull()
                            ->placeholder('Masukkan alasan')
                            ->live()
                            ->visible(function ($get) {
                                return $get('status') == 'absen';
                            }),
                    ])
                    ->extraAttributes([
                        'class' => 'mb-5'
                    ])
                    ->columns(1)
            ])
            ->statePath('data');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->schedule)
            ->schema([
                ComponentsFieldset::make('Informasi Meeting')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada deskripsi'),
                        Split::make([
                            TextEntry::make('start_at')
                                ->label('Waktu Mulai')
                                ->formatStateUsing(function ($state) {
                                    return Utils::dateReadable($state);
                                }),
                            TextEntry::make('end_at')
                                ->label('Waktu Selesai')
                                ->formatStateUsing(function ($state) {
                                    return Utils::dateReadable($state);
                                }),
                        ])
                    ])->columns(1),
                ComponentsFieldset::make('Informasi Ruangan')
                    ->schema([
                        TextEntry::make('room.name')
                            ->label('Ruangan'),
                        Split::make([
                            TextEntry::make('room.location')
                                ->label('Lokasi'),
                            TextEntry::make('room.capacity')
                                ->label('Kapasitas'),
                        ])
                    ])->columns(1)
            ]);
    }
}
