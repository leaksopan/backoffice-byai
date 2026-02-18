<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Modules\MasterDataManagement\Filament\Resources\FundingSourceResource\Pages;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FundingSourceResource extends Resource
{
    protected static ?string $model = MdmFundingSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Funding Sources';

    protected static ?string $modelLabel = 'Funding Source';

    protected static ?string $pluralModelLabel = 'Funding Sources';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Funding Source Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->label('Code')
                            ->placeholder('e.g., FS-001'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Name')
                            ->placeholder('e.g., APBN 2026'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'apbn' => 'APBN',
                                'apbd_provinsi' => 'APBD Provinsi',
                                'apbd_kab_kota' => 'APBD Kabupaten/Kota',
                                'pnbp' => 'PNBP',
                                'hibah' => 'Hibah',
                                'pinjaman' => 'Pinjaman',
                                'lainnya' => 'Lain-lain',
                            ])
                            ->label('Type')
                            ->native(false),

                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->label('Start Date')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Leave empty for indefinite period')
                            ->afterOrEqual('start_date'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'apbn' => 'success',
                        'apbd_provinsi' => 'info',
                        'apbd_kab_kota' => 'info',
                        'pnbp' => 'warning',
                        'hibah' => 'primary',
                        'pinjaman' => 'danger',
                        'lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (MdmFundingSource $record): string => $record->getTypeLabel())
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d/m/Y')
                    ->placeholder('Indefinite')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'apbn' => 'APBN',
                        'apbd_provinsi' => 'APBD Provinsi',
                        'apbd_kab_kota' => 'APBD Kabupaten/Kota',
                        'pnbp' => 'PNBP',
                        'hibah' => 'Hibah',
                        'pinjaman' => 'Pinjaman',
                        'lainnya' => 'Lain-lain',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundingSources::route('/'),
            'create' => Pages\CreateFundingSource::route('/create'),
            'edit' => Pages\EditFundingSource::route('/{record}/edit'),
        ];
    }
}
