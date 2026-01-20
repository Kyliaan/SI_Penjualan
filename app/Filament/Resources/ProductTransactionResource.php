<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use App\Models\ProductTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
     {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('city')
                            ->required(),

                        Forms\Components\TextInput::make('post_code')
                            ->required(),

                        Forms\Components\Textarea::make('address')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Transaction Detail')
                    ->schema([
                        Forms\Components\TextInput::make('booking_trx_id')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('produk_id')
                            ->relationship('produk', 'name')
                            ->required(),

                        Forms\Components\TextInput::make('produk_size')
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('sub_total_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\TextInput::make('grand_total_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Select::make('promo_code_id')
                            ->relationship('promoCode', 'code')
                            ->nullable(),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Paid Status')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Proof')
                    ->schema([
                        Forms\Components\FileUpload::make('proof')
                            ->image()
                            ->directory('payment-proofs')
                            ->maxSize(2048)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('booking_trx_id')
                ->label('TRX ID')
                ->searchable(),

            Tables\Columns\TextColumn::make('name')
                ->label('Customer')
                ->searchable(),

            Tables\Columns\TextColumn::make('phone')
                ->label('Phone'),

            Tables\Columns\TextColumn::make('email')
                ->label('Email'),

            Tables\Columns\TextColumn::make('produk.name')
                ->label('Product'),

            Tables\Columns\TextColumn::make('produk_size')
                ->label('Size'),

            Tables\Columns\TextColumn::make('quantity')
                ->label('Qty'),

            Tables\Columns\TextColumn::make('sub_total_amount')
                ->label('Sub Total')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('grand_total_amount')
                ->label('Grand Total')
                ->money('IDR'),

            Tables\Columns\ImageColumn::make('proof')
                ->label('Proof')
                ->circular(),

            Tables\Columns\IconColumn::make('is_paid')
                ->label('Paid')
                ->boolean(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                ->dateTime('d M Y'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]),
        ]);
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
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
        ];
    }
}
