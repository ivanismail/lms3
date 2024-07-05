<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Models\Room;
use App\Models\Schedule;
use App\Utils\Utils;
use Carbon\Carbon;
use DateTime;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use JaOcero\RadioDeck\Forms\Components\RadioDeck;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    function availableSchedule($get)
    {
        $start = $get('start_at') ?? now();
        $end = $get('end_at') ?? now();

        $scheduleRoomIds = Schedule::query()
            ->where(function ($query) use ($start, $end) {
                return $query
                    ->whereDate('start_at', '<=', Carbon::make($end)->toDateString())
                    ->whereDate('end_at', '>=', Carbon::make($start)->toDateString());
            })
            ->where(function ($query) use ($start, $end) {
                return $query
                    ->whereTime('start_at', '<', Carbon::make($end)->toTimeString())
                    ->whereTime('end_at', '>', Carbon::make($start)->toTimeString());
            })
            ->pluck('room_id');

        return $scheduleRoomIds;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create')
                ->label('Buat Jadwal')
                ->modalSubmitActionLabel('Simpan')
                ->steps([
                    Wizard\Step::make('Waktu Meeting')
                        ->schema([
                            DateTimePicker::make('start_at')
                                ->label('Waktu Mulai Meeting')
                                ->validationAttribute('Waktu Mulai Meeting')
                                ->seconds(false)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('end_at', null);
                                    $set('room_id', null);
                                }),
                            DateTimePicker::make('end_at')
                                ->label('Waktu Selesai Meeting')
                                ->validationAttribute('Waktu Selesai Meeting')
                                ->required()
                                ->seconds(false)
                                ->after('start_at')
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('room_id', null);
                                }),
                        ])
                        ->columns(2),
                    Wizard\Step::make('Ruangan Tersedia')
                        ->schema([
                            Fieldset::make('Waktu Meeting')
                                ->schema([
                                    Placeholder::make('')
                                        ->label('Mulai')
                                        ->content(function ($get) {
                                            return Utils::dateReadable($get('start_at'));
                                        }),
                                    Placeholder::make('')
                                        ->label('Selesai')
                                        ->content(function ($get) {
                                            return Utils::dateReadable($get('end_at'));
                                        }),
                                ])
                                ->columns(2),
                            RadioDeck::make('room_id')
                                ->label('Ruangan yang tersedia')
                                ->options(function ($get) {
                                    $scheduleRoomIds = $this->availableSchedule($get);

                                    return Room::get()
                                        ->whereNotIn('id', $scheduleRoomIds)
                                        ->pluck('name', 'id');
                                })
                                ->descriptions(function ($get) {
                                    $scheduleRoomIds = $this->availableSchedule($get);

                                    return Room::query()
                                        ->selectRaw('CONCAT("Kapasitas: ", capacity, " Orang <br> Lokasi: ", location) AS mapping, id, name')
                                        ->whereNotIn('id', $scheduleRoomIds)
                                        ->pluck('mapping', 'id')
                                        ->map(function ($data) {
                                            return new HtmlString($data);
                                        });
                                })
                                ->required()
                                ->alignment(Alignment::Start) // Start | Center | End | (string - start | center | end)
                                ->gap('gap-5') // Gap between Icon and Description (Any TailwindCSS gap-* utility)
                                // ->padding('px-4 px-6') // Padding around the deck (Any TailwindCSS padding utility)
                                ->direction('column') // Column | Row (Allows to place the Icon on top)
                                ->extraCardsAttributes([ // Extra Attributes to add to the card HTML element
                                    // 'class' => 'rounded-xl pt-2'
                                ])
                                ->extraOptionsAttributes([ // Extra Attributes to add to the option HTML element
                                    // 'class' => 'text-3xl leading-none w-full flex flex-col items-center justify-center p-4'
                                ])
                                ->extraDescriptionsAttributes([ // Extra Attributes to add to the description HTML element
                                    // 'class' => 'text-sm font-light text-center'
                                ])
                                ->color('primary')
                                ->columns(3)
                        ]),
                    Wizard\Step::make('Informasi Meeting')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama Meeting')
                                ->validationAttribute('Nama Meeting')
                                ->placeholder('Masukkan nama meeting')
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('code')
                                ->label('Kode Meeting')
                                ->validationAttribute('Kode Meeting')
                                ->placeholder('Masukkan kode meeting')
                                ->columnSpanFull()
                                ->required()
                                ->live()
                                ->afterStateHydrated(function ($set) {
                                    $code = Utils::randomStr(9);
                                    $code = str_split($code, 3);
                                    $code = join('-', $code);

                                    $set('code', $code);
                                })
                                ->unique(
                                    table: 'schedules',
                                    column: 'code',
                                    ignoreRecord: true,
                                ),
                            Textarea::make('description')
                                ->label('Deskripsi Meeting')
                                ->validationAttribute('Deskripsi Meeting')
                                ->columnSpanFull()
                                ->placeholder('Ketik deskripsi meeting'),
                        ]),
                ])
                ->using(function ($data, $action) {
                    $startAt = Carbon::make($data['start_at']);
                    $endAt = Carbon::make($data['end_at']);

                    $data['user_id'] = Auth::user()->id;
                    $data['link'] = 'Ini adalah link';

                    $minutes = $startAt->diffInMinutes($endAt);
                    $data['minutes'] = (int) $minutes;

                    $model = Schedule::create($data);

                    return $model;
                })
                ->successRedirectUrl(function ($record) {
                    return EditSchedule::getUrl([
                        'record' => $record->id
                    ]);
                }),
        ];
    }
}
