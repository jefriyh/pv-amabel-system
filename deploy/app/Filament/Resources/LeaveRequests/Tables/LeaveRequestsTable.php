<?php

namespace App\Filament\Resources\LeaveRequests\Tables;

use App\Http\Controllers\Admin\MediaController;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeaveRequestsTable
{
    public static function configure(Table $table): Table
    {
        $currentUser = auth()->user();
        $canApprove = $currentUser?->isSuperAdmin() || $currentUser?->isPengurus();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Petugas & Jadwal')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (LeaveRequest $record) {
                        $dates = $record->formatted_selected_dates;
                        $duration = "{$record->total_days} Hari";
                        $reason = $record->reason ? " | Alasan: {$record->reason}" : '';

                        return "{$dates} ({$duration}){$reason}";
                    })
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeaveRequest::getTypeLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        LeaveRequest::TYPE_CUTI => 'info',
                        LeaveRequest::TYPE_IZIN_DARURAT => 'warning',
                        LeaveRequest::TYPE_SAKIT => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeaveRequest::getStatusLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        LeaveRequest::STATUS_PENDING => 'warning',
                        LeaveRequest::STATUS_APPROVED => 'success',
                        LeaveRequest::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                ImageColumn::make('attachment_path')
                    ->label('Lampiran')
                    ->circular()
                    ->size(36)
                    ->state(fn ($record) => MediaController::urlFor($record, 'attachment_path'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('approver.name')
                    ->label('Penyetuju')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approval_notes')
                    ->label('Catatan Approval')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LeaveRequest::getStatusLabels()),

                SelectFilter::make('type')
                    ->label('Jenis Pengajuan')
                    ->options(LeaveRequest::getTypeLabels()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record): bool => $canApprove && $record->status === LeaveRequest::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Persetujuan Pengajuan')
                    ->modalDescription(fn (LeaveRequest $record) => "Setujui {$record->type_label} untuk {$record->user->name} selama {$record->total_days} hari?")
                    ->form([
                        Textarea::make('approval_notes')
                            ->label('Catatan Persetujuan (Opsional)')
                            ->placeholder('Contoh: Disetujui, harap serah terima tugas sebelum cuti.'),
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveRequest::STATUS_APPROVED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Pengajuan berhasil disetujui')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record): bool => $canApprove && $record->status === LeaveRequest::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan')
                    ->form([
                        Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Jelaskan alasan penolakan pengajuan ini...'),
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveRequest::STATUS_REJECTED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'],
                        ]);

                        Notification::make()
                            ->title('Pengajuan ditolak')
                            ->danger()
                            ->send();
                    }),

                ViewAction::make()->iconButton()->tooltip('Lihat Detail'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Ubah')
                    ->visible(fn (LeaveRequest $record): bool => auth()->user()?->isSuperAdmin() || ($record->status === LeaveRequest::STATUS_PENDING && $record->user_id === auth()->id())),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->visible(fn (LeaveRequest $record): bool => auth()->user()?->isSuperAdmin() || ($record->status === LeaveRequest::STATUS_PENDING && $record->user_id === auth()->id())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                ]),
            ])
            ->emptyStateHeading('Belum ada pengajuan cuti/izin/sakit')
            ->emptyStateDescription('Security dapat mengajukan cuti, izin darurat, atau surat sakit di sini.');
    }
}
