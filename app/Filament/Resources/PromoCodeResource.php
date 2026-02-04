<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoCodeResource extends Resource
{
    // Model utama
    protected static ?string $model = PromoCode::class;

    // Konfigurasi menu sidebar
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Promo Codes';
    protected static ?string $pluralModelLabel = 'Promo Codes';

    // FORM
    public static function form(Form $form): Form
    {
        return $form->schema([

            // Kode promo
            Forms\Components\TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            // Nominal diskon
            Forms\Components\TextInput::make('discount_amount')
                ->label('Discount Amount')
                ->numeric()
                ->prefix('IDR')
                ->required(),
        ]);
    }

    // TABLE
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])

            // Filter data terhapus
            ->filters([
                TrashedFilter::make(),
            ])

            // Aksi per baris
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make(), // 👈 restore

                Tables\Actions\ForceDeleteAction::make() // 🔥 HAPUS PERMANEN
        ->requiresConfirmation(),
            ])

            // Aksi massal
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(), // 👈 restore massal
                     Tables\Actions\ForceDeleteBulkAction::make(), // 🔥 hapus permanen massal

                ]),
            ]);
    }

    // Query supaya bisa baca data soft delete
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // PAGES
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit'   => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
