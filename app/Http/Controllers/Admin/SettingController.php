<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = $this->getSettings();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,webp|max:512',
            'currency' => 'required|string|max:3',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'shipping_fee' => 'required|numeric|min:0',
            'free_shipping_min' => 'required|numeric|min:0',
            'payment_methods' => 'array',
            'payment_methods.*' => 'string|in:credit_card,debit_card,pix,bank_slip',
            'order_status_emails' => 'boolean',
            'stock_alerts' => 'boolean',
            'maintenance_mode' => 'boolean',
            'analytics_code' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $settings = $this->getSettings();

        // Update basic settings
        $settings['site_name'] = $request->site_name;
        $settings['site_description'] = $request->site_description;
        $settings['contact_email'] = $request->contact_email;
        $settings['contact_phone'] = $request->contact_phone;
        $settings['address'] = $request->address;
        $settings['currency'] = $request->currency;
        $settings['tax_rate'] = $request->tax_rate;
        $settings['shipping_fee'] = $request->shipping_fee;
        $settings['free_shipping_min'] = $request->free_shipping_min;
        $settings['payment_methods'] = $request->payment_methods ?? [];
        $settings['order_status_emails'] = $request->boolean('order_status_emails');
        $settings['stock_alerts'] = $request->boolean('stock_alerts');
        $settings['maintenance_mode'] = $request->boolean('maintenance_mode');
        $settings['analytics_code'] = $request->analytics_code;
        $settings['facebook_url'] = $request->facebook_url;
        $settings['instagram_url'] = $request->instagram_url;
        $settings['twitter_url'] = $request->twitter_url;
        $settings['whatsapp_number'] = $request->whatsapp_number;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if (isset($settings['logo']) && Storage::exists('public/' . $settings['logo'])) {
                Storage::delete('public/' . $settings['logo']);
            }
            $logoPath = $request->file('logo')->store('settings', 'public');
            $settings['logo'] = $logoPath;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            if (isset($settings['favicon']) && Storage::exists('public/' . $settings['favicon'])) {
                Storage::delete('public/' . $settings['favicon']);
            }
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            $settings['favicon'] = $faviconPath;
        }

        $this->saveSettings($settings);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Backup database.
     */
    public function backup()
    {
        try {
            $databasePath = database_path('database.sqlite');
            
            if (!file_exists($databasePath)) {
                return redirect()->back()->with('error', 'Arquivo de banco de dados não encontrado.');
            }

            $backupPath = storage_path('backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
            $backupFullPath = $backupPath . '/' . $backupFileName;

            if (copy($databasePath, $backupFullPath)) {
                return response()->download($backupFullPath)->deleteFileAfterSend();
            } else {
                return redirect()->back()->with('error', 'Erro ao criar backup do banco de dados.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar backup: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return redirect()->back()->with('success', 'Cache limpo com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao limpar cache: ' . $e->getMessage());
        }
    }

    /**
     * Test email settings.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw('Este é um email de teste do sistema da loja.', function ($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Teste de Email - Sistema da Loja');
            });

            return redirect()->back()->with('success', 'Email de teste enviado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Get system info.
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'timezone' => date_default_timezone_get(),
        ];

        return response()->json($info);
    }

    /**
     * Get application settings.
     */
    private function getSettings()
    {
        $defaultSettings = [
            'site_name' => 'Minha Loja',
            'site_description' => 'A melhor loja online do Brasil',
            'contact_email' => 'contato@minhaloja.com',
            'contact_phone' => '(11) 99999-9999',
            'address' => 'Rua das Flores, 123 - São Paulo, SP',
            'logo' => null,
            'favicon' => null,
            'currency' => 'BRL',
            'tax_rate' => 0,
            'shipping_fee' => 10.00,
            'free_shipping_min' => 100.00,
            'payment_methods' => ['credit_card', 'pix'],
            'order_status_emails' => true,
            'stock_alerts' => true,
            'maintenance_mode' => false,
            'analytics_code' => null,
            'facebook_url' => null,
            'instagram_url' => null,
            'twitter_url' => null,
            'whatsapp_number' => null,
        ];

        $settingsFile = storage_path('app/settings.json');
        
        if (file_exists($settingsFile)) {
            $savedSettings = json_decode(file_get_contents($settingsFile), true);
            return array_merge($defaultSettings, $savedSettings);
        }

        return $defaultSettings;
    }

    /**
     * Save application settings.
     */
    private function saveSettings($settings)
    {
        $settingsFile = storage_path('app/settings.json');
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize()
    {
        try {
            $databasePath = database_path('database.sqlite');
            if (file_exists($databasePath)) {
                $sizeBytes = filesize($databasePath);
                return $this->formatBytes($sizeBytes);
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get storage used.
     */
    private function getStorageUsed()
    {
        try {
            $storagePath = storage_path('app/public');
            if (is_dir($storagePath)) {
                $sizeBytes = $this->getDirectorySize($storagePath);
                return $this->formatBytes($sizeBytes);
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get directory size recursively.
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
