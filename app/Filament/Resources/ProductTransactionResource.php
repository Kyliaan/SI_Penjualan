<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Models\ProductTransaction;
use App\Models\Produk;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductTransactionResource extends Resource
{
    // Model utama resource
    protected static ?string $model = ProductTransaction::class;

    // Icon sidebar
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    // FORM
    public static function form(Form $form): Form
    {
        return $form->schema([

            // Wizard = form bertahap
            Forms\Components\Wizard::make([

                // STEP 1: PRODUK & HARGA
                Forms\Components\Wizard\Step::make('Product and Price')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([

                            // Pilih produk
                            Forms\Components\Select::make('produk_id')
                                ->label('Product')
                                ->relationship('produk', 'name') // ambil relasi produk
                                ->required()                     // wajib dipilih
                                ->searchable()                   // bisa dicari
                                ->preload()                      // load data awal
                                ->live()                         // realtime
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $produk = Produk::find($state); // ambil produk

                                    $price = $produk?->price ?? 0; // harga produk
                                    $quantity = $get('quantity') ?? 1; // qty default

                                    $subTotal = $price * $quantity; // hitung subtotal

                                    $set('price', $price);                 // set harga
                                    $set('sub_total_amount', $subTotal);   // set subtotal
                                    $set('grand_total_amount', $subTotal); // set total awal

                                    // Ambil size produk
                                    $sizes = $produk
                                        ? $produk->sizes->pluck('size', 'id')->toArray()
                                        : [];

                                    $set('produk_sizes', $sizes); // set size
                                }),

                            // Pilih size
                            Forms\Components\Select::make('produk_size')
                                ->label('Size')
                                ->options(fn (Forms\Get $get) => $get('produk_sizes') ?? []) // opsi dinamis
                                ->required() // wajib dipilih
                                ->live(),    // realtime

                            // Jumlah beli
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()   // hanya angka
                                ->required()  // wajib diisi
                                ->minValue(1) // minimal 1
                                ->live()      // realtime
                                ->helperText(function (Forms\Get $get) {
                                    $produkId = $get('produk_id'); // ambil produk

                                    if (! $produkId) {
                                        return 'Pilih produk terlebih dahulu';
                                    }

                                    $produk = Produk::find($produkId);

                                    return $produk ? 'Stok tersedia: '.$produk->stock : '';
                                })
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $price = $get('price') ?? 0; // ambil harga
                                    $quantity = $state ?? 1;    // qty terbaru

                                    $subTotal = $price * $quantity; // hitung subtotal
                                    $set('sub_total_amount', $subTotal); // update subtotal

                                    $discount = $get('discount_amount') ?? 0; // ambil diskon
                                    $set(
                                        'grand_total_amount',
                                        max($subTotal - $discount, 0) // total akhir
                                    );
                                }),

                            // Pilih promo
                            Forms\Components\Select::make('promo_code_id')
                                ->label('Promo Code')
                                ->relationship('promoCode', 'code') // relasi promo
                                ->searchable() // bisa dicari
                                ->preload()    // load awal
                                ->live()       // realtime
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $subTotal = $get('sub_total_amount') ?? 0; // subtotal
                                    $discount = PromoCode::find($state)?->discount_amount ?? 0; // diskon

                                    $set('discount_amount', $discount); // set diskon
                                    $set(
                                        'grand_total_amount',
                                        max($subTotal - $discount, 0) // total akhir
                                    );
                                }),

                            // Subtotal
                            Forms\Components\TextInput::make('sub_total_amount')
                                ->numeric()   // format angka
                                ->readOnly()  // tidak bisa diedit
                                ->prefix('IDR'), // mata uang

                            // Total akhir
                            Forms\Components\TextInput::make('grand_total_amount')
                                ->numeric()
                                ->readOnly()
                                ->prefix('IDR'),

                            // Diskon
                            Forms\Components\TextInput::make('discount_amount')
                                ->label('Discount Amount')
                                ->numeric()
                                ->readOnly() // hasil hitung
                                ->prefix('IDR')
                                ->default(0) // nilai awal
                                ->dehydrated(false), // tidak disimpan ke DB
                        ]),
                    ]),

                // STEP 2: DATA CUSTOMER
                Forms\Components\Wizard\Step::make('Customer Information')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([

                            Forms\Components\TextInput::make('name')
                                ->required() // wajib
                                ->maxLength(255), // batas karakter

                            Forms\Components\TextInput::make('email')
                                ->email()   // validasi email
                                ->required(),

                            Forms\Components\TextInput::make('phone')
                                ->required()
                                ->numeric() // hanya angka
                                ->maxLength(20),

                            Forms\Components\TextInput::make('city')
                                ->required(),

                            Forms\Components\TextInput::make('post_code')
                                ->required()
                                ->numeric(),

                            Forms\Components\Textarea::make('address')
                                ->columnSpanFull() // satu baris penuh
                                ->required(),
                        ]),
                    ]),

                // ===== STEP 3: PEMBAYARAN =====
                Forms\Components\Wizard\Step::make('Payment Information')
                    ->schema([

                        // ID transaksi otomatis
                        Forms\Components\TextInput::make('booking_trx_id')
                            ->default(fn () => ProductTransaction::generateUniqueTrxId())
                            ->required()
                            ->disabled()   // tidak bisa diedit
                            ->dehydrated(), // tetap disimpan

                        // Status bayar
                        Forms\Components\ToggleButtons::make('is_paid')
                            ->label('Is Paid?')
                            ->boolean() // true / false
                            ->grouped()
                            ->required()
                            ->live(),

                        // Bukti pembayaran
                        Forms\Components\FileUpload::make('proof')
                            ->label('Payment Proof')
                            ->image()
                            ->directory('payment-proofs')
                            ->reactive() // WAJIB
                            ->visible(fn (Forms\Get $get) => $get('is_paid'))
                            ->required(fn (Forms\Get $get) => $get('is_paid')), // simpan jika paid
                    ])
                    ->columns(1),
            ])
                ->columnSpan('full') // lebar penuh
                ->skippable(),       // step bisa dilewati
        ]);
    }

    // TABLE
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Gambar produk
                Tables\Columns\ImageColumn::make('produk.thumbnail')
                    ->label('Product')
                    ->circular(),

                // Nama customer
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable(),

                // ID transaksi
                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->label('Booking Trx ID')
                    ->searchable(),

                // Status bayar
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->label('Paid'),
            ])

            // Filter produk
            ->filters([
                Tables\Filters\SelectFilter::make('produk_id')
                    ->relationship('produk', 'name')
                    ->label('Product'),
            ])

            // Aksi per baris
            ->actions([
                Tables\Actions\EditAction::make(),   // edit
                Tables\Actions\DeleteAction::make(), // hapus

                // Approve pembayaran
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->visible(fn (ProductTransaction $record) => ! $record->is_paid)
                    ->requiresConfirmation()
                    ->action(function (ProductTransaction $record) {
                        $record->is_paid = true; // set paid
                        $record->save();
                    }),

                // Download bukti bayar
                Tables\Actions\Action::make('download_proof')
                    ->label('Download Proof')
                    ->visible(fn (ProductTransaction $record) => ! empty($record->proof))
                    ->action(fn (ProductTransaction $record) => response()->download(
                        storage_path('app/public/'.$record->proof)
                    )
                    ),
            ])

            // Hapus banyak data
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // PAGES
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
        ];
    }
}
