<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('internal')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('Villa Amabel - Internal Portal')
            ->colors([
                'primary' => '#5F8575',
                'gray' => Color::Slate,
                'info' => '#3BA2B8',
                'success' => '#10B981',
                'warning' => '#F0950C',
                'danger' => '#E11D48',
            ])
            ->font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->favicon(asset('favicon.jpg'))
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => Blade::render('
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                    <link rel="stylesheet" href="' . asset('css/tailadmin-theme.css') . '?v=' . time() . '">
                    <style>
                        .fi-sidebar-header {
                            padding: 1.75rem 1.5rem !important;
                            min-height: 5.25rem !important;
                            display: flex !important;
                            align-items: center !important;
                        }
                        .fi-sidebar-header .fi-logo,
                        .fi-sidebar-header a,
                        .fi-sidebar-header span,
                        .fi-sidebar-header .fi-brand-name {
                            font-size: 1.15rem !important;
                            line-height: 1.45 !important;
                            font-weight: 800 !important;
                            letter-spacing: -0.01em !important;
                            color: #FFFFFF !important;
                            display: block !important;
                            padding: 0.25rem 0 !important;
                        }
                        .fi-sidebar-nav {
                            padding: 1.25rem 0.85rem !important;
                        }
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
                        .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] {
                            background-color: #ffffff !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                        }
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label,
                        .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] > .fi-sidebar-item-label {
                            color: #111827 !important;
                            font-weight: 700 !important;
                        }
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon,
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-btn svg,
                        .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] > .fi-icon,
                        .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] svg {
                            color: #1F3A2E !important;
                        }
                        .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn > .fi-sidebar-item-label {
                            color: #ffffff !important;
                        }
                        /* Sidebar Badges / Notification Counts (Single Clean Pill) */
                        .fi-sidebar-item-badge-ctn {
                            background: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                        aside.fi-sidebar .fi-sidebar-item-btn .fi-badge,
                        aside.fi-sidebar .fi-badge {
                            background-color: #24352D !important;
                            border: 1px solid rgba(255, 255, 255, 0.15) !important;
                            border-radius: 9999px !important;
                            padding: 0.1rem 0.5rem !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            box-shadow: none !important;
                        }
                        aside.fi-sidebar .fi-sidebar-item-btn .fi-badge span,
                        aside.fi-sidebar .fi-sidebar-item-btn .fi-badge .fi-badge-label-ctn,
                        aside.fi-sidebar .fi-badge span,
                        aside.fi-sidebar .fi-badge-label-ctn {
                            color: #6EE7B7 !important;
                            font-weight: 700 !important;
                            font-size: 0.75rem !important;
                            line-height: 1.25 !important;
                        }
                        .fi-sidebar-item.fi-active .fi-badge,
                        .fi-sidebar-item.fi-sidebar-item-active .fi-badge,
                        aside.fi-sidebar [aria-current="page"] .fi-badge {
                            background-color: #5F8575 !important;
                            border: none !important;
                        }
                        .fi-sidebar-item.fi-active .fi-badge span,
                        .fi-sidebar-item.fi-sidebar-item-active .fi-badge span,
                        aside.fi-sidebar [aria-current="page"] .fi-badge span {
                            color: #ffffff !important;
                            font-weight: 700 !important;
                        }
                        .fi-page,
                        .fi-page-header-main-ctn,
                        .fi-page-main,
                        .fi-page-content,
                        .fi-form,
                        .fi-section,
                        .fi-card {
                            max-width: 100% !important;
                            width: 100% !important;
                        }
                        .filepond--drop-label label {
                            font-size: 0 !important;
                            line-height: 0 !important;
                        }
                        .filepond--drop-label label::after {
                            content: "📷 Ambil Foto / Jelajahi" !important;
                            font-size: 0.875rem !important;
                            line-height: 1.25rem !important;
                            font-weight: 600 !important;
                            color: #436354 !important;
                            display: inline-block !important;
                            padding: 0.5rem 1.25rem !important;
                            border-radius: 0.5rem !important;
                            background-color: rgba(67, 99, 84, 0.08) !important;
                            border: 1.5px dashed #436354 !important;
                            cursor: pointer !important;
                        }
                    </style>
                ')
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => Blade::render('
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                    <script>
                        window.shareSingleAttendanceScreenshot = async function(cardId, btnId, btnTextId, alertId, waUrl, textCaption) {
                            const btn = document.getElementById(btnId);
                            const btnText = document.getElementById(btnTextId);
                            const alertBox = alertId ? document.getElementById(alertId) : null;

                            const originalText = btnText ? btnText.innerText : "";
                            if (btn && btnText) {
                                btn.disabled = true;
                                btnText.innerText = "Mengambil Screenshot...";
                            }

                            try {
                                const card = document.getElementById(cardId);
                                if (!card) throw new Error("Card element not found: " + cardId);

                                if (typeof html2canvas === "undefined") {
                                    await new Promise((resolve, reject) => {
                                        const s = document.createElement("script");
                                        s.src = "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
                                        s.onload = resolve;
                                        s.onerror = reject;
                                        document.head.appendChild(s);
                                    });
                                }

                                const canvas = await html2canvas(card, {
                                    scale: 2,
                                    useCORS: true,
                                    allowTaint: false,
                                    backgroundColor: "#ffffff",
                                    logging: false
                                });

                                canvas.toBlob(async (blob) => {
                                    if (!blob) {
                                        window.open(waUrl, "_blank");
                                        if (btn && btnText) {
                                            btnText.innerText = originalText;
                                            btn.disabled = false;
                                        }
                                        return;
                                    }

                                    const fileName = "Presensi-Security-" + (new Date().toISOString().slice(0, 10)) + ".png";
                                    const file = new File([blob], fileName, { type: "image/png" });

                                    // 1. Web Share API (HP Android/iOS)
                                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                                        try {
                                            await navigator.share({
                                                files: [file],
                                                title: "Presensi Kehadiran Security",
                                                text: textCaption
                                            });
                                            if (btn && btnText) {
                                                btnText.innerText = originalText;
                                                btn.disabled = false;
                                            }
                                            return;
                                        } catch (shareErr) {
                                            if (shareErr.name === "AbortError") {
                                                if (btn && btnText) {
                                                    btnText.innerText = originalText;
                                                    btn.disabled = false;
                                                }
                                                return;
                                            }
                                            console.warn("Web Share failed, fallback to clipboard", shareErr);
                                        }
                                    }

                                    // 2. Fallback Clipboard & Auto Download
                                    let copied = false;
                                    if (navigator.clipboard && window.ClipboardItem) {
                                        try {
                                            await navigator.clipboard.write([
                                                new ClipboardItem({ "image/png": blob })
                                            ]);
                                            copied = true;
                                        } catch (clipErr) {
                                            console.warn("Clipboard write failed", clipErr);
                                        }
                                    }

                                    try {
                                        const downloadLink = document.createElement("a");
                                        downloadLink.download = fileName;
                                        downloadLink.href = URL.createObjectURL(blob);
                                        downloadLink.click();
                                    } catch (dlErr) {
                                        console.warn("Download failed", dlErr);
                                    }

                                    // Buka chat WhatsApp
                                    window.open(waUrl, "_blank");

                                    if (alertBox) {
                                        alertBox.style.display = "block";
                                        if (copied) {
                                            alertBox.innerText = "✅ Gambar screenshot disalin ke clipboard! Langsung Paste (Ctrl+V) di chat WhatsApp.";
                                        } else {
                                            alertBox.innerText = "✅ Gambar screenshot berhasil diunduh! Silakan lampirkan gambar di chat WhatsApp.";
                                        }
                                    }

                                    if (btn && btnText) {
                                        btnText.innerText = originalText;
                                        btn.disabled = false;
                                    }
                                }, "image/png");

                            } catch (err) {
                                console.error("Failed capturing screenshot", err);
                                window.open(waUrl, "_blank");
                                if (btn && btnText) {
                                    btnText.innerText = originalText;
                                    btn.disabled = false;
                                }
                            }
                        };

                        window.shareAttendanceCardScreenshot = function(waUrl, textCaption) {
                            return window.shareSingleAttendanceScreenshot("attendance-popup-card", "btn-share-whatsapp-screenshot", "btn-share-text", "attendance-screenshot-alert", waUrl, textCaption);
                        };
                    </script>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
