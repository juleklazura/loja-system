<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogViewerController extends Controller
{
    /**
     * Lista todos os arquivos de log disponíveis
     */
    public function index()
    {
        $logPath = storage_path('logs');
        $logFiles = collect(File::files($logPath))
            ->filter(function ($file) {
                return Str::endsWith($file->getFilename(), '.log');
            })
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'type' => $this->getLogType($file->getFilename()),
                ];
            })
            ->sortByDesc('modified')
            ->values();

        return view('admin.logs.index', compact('logFiles'));
    }

    /**
     * Exibe o conteúdo de um arquivo de log específico
     */
    public function show(Request $request, string $filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !Str::endsWith($filename, '.log')) {
            abort(404, 'Arquivo de log não encontrado');
        }

        $lines = $request->get('lines', 100);
        $search = $request->get('search');
        $level = $request->get('level');

        $content = $this->getLogContent($logPath, $lines, $search, $level);
        
        return view('admin.logs.show', [
            'filename' => $filename,
            'content' => $content,
            'lines' => $lines,
            'search' => $search,
            'level' => $level,
        ]);
    }

    /**
     * Baixa um arquivo de log
     */
    public function download(string $filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !Str::endsWith($filename, '.log')) {
            abort(404, 'Arquivo de log não encontrado');
        }

        return response()->download($logPath);
    }

    /**
     * Limpa um arquivo de log específico
     */
    public function clear(string $filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !Str::endsWith($filename, '.log')) {
            abort(404, 'Arquivo de log não encontrado');
        }

        File::put($logPath, '');

        return redirect()->route('admin.logs.show', $filename)
            ->with('success', "Log {$filename} foi limpo com sucesso");
    }

    /**
     * Exclui um arquivo de log
     */
    public function delete(string $filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !Str::endsWith($filename, '.log')) {
            abort(404, 'Arquivo de log não encontrado');
        }

        File::delete($logPath);

        return redirect()->route('admin.logs.index')
            ->with('success', "Log {$filename} foi excluído com sucesso");
    }

    /**
     * API endpoint para busca em tempo real
     */
    public function search(Request $request)
    {
        $filename = $request->get('file');
        $search = $request->get('search');
        $level = $request->get('level');
        $lines = $request->get('lines', 50);

        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath)) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        $content = $this->getLogContent($logPath, $lines, $search, $level);

        return response()->json([
            'content' => $content,
            'total_lines' => count(file($logPath)),
        ]);
    }

    /**
     * Obtém estatísticas dos logs
     */
    public function stats()
    {
        $logPath = storage_path('logs');
        $stats = [];

        $logFiles = File::files($logPath);
        
        foreach ($logFiles as $file) {
            if (!Str::endsWith($file->getFilename(), '.log')) {
                continue;
            }

            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);
            
            $levelCounts = [
                'ERROR' => 0,
                'WARNING' => 0,
                'INFO' => 0,
                'DEBUG' => 0,
            ];

            foreach ($lines as $line) {
                foreach ($levelCounts as $level => $count) {
                    if (Str::contains($line, ".$level:")) {
                        $levelCounts[$level]++;
                    }
                }
            }

            $stats[$file->getFilename()] = [
                'total_lines' => count($lines),
                'size' => $this->formatBytes($file->getSize()),
                'levels' => $levelCounts,
                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        return view('admin.logs.stats', compact('stats'));
    }

    /**
     * Obtém o conteúdo filtrado do log
     */
    private function getLogContent(string $logPath, int $lines, ?string $search, ?string $level): array
    {
        $content = File::get($logPath);
        $allLines = explode("\n", $content);
        
        // Filtrar por nível se especificado
        if ($level) {
            $allLines = array_filter($allLines, function ($line) use ($level) {
                return Str::contains($line, ".$level:");
            });
        }

        // Filtrar por busca se especificado
        if ($search) {
            $allLines = array_filter($allLines, function ($line) use ($search) {
                return Str::contains(strtolower($line), strtolower($search));
            });
        }

        // Pegar as últimas N linhas
        $filteredLines = array_slice(array_reverse($allLines), 0, $lines);
        
        return array_map(function ($line, $index) {
            return [
                'number' => $index + 1,
                'content' => $line,
                'level' => $this->extractLogLevel($line),
                'timestamp' => $this->extractTimestamp($line),
            ];
        }, $filteredLines, array_keys($filteredLines));
    }

    /**
     * Formata bytes em formato legível
     */
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Determina o tipo de log baseado no nome do arquivo
     */
    private function getLogType(string $filename): string
    {
        if (Str::contains($filename, 'cart')) return 'cart';
        if (Str::contains($filename, 'auth')) return 'auth';
        if (Str::contains($filename, 'audit')) return 'audit';
        if (Str::contains($filename, 'performance')) return 'performance';
        if (Str::contains($filename, 'security')) return 'security';
        if (Str::contains($filename, 'api')) return 'api';
        if (Str::contains($filename, 'database')) return 'database';
        
        return 'general';
    }

    /**
     * Extrai o nível do log da linha
     */
    private function extractLogLevel(string $line): string
    {
        if (Str::contains($line, '.ERROR:')) return 'ERROR';
        if (Str::contains($line, '.WARNING:')) return 'WARNING';
        if (Str::contains($line, '.INFO:')) return 'INFO';
        if (Str::contains($line, '.DEBUG:')) return 'DEBUG';
        
        return 'UNKNOWN';
    }

    /**
     * Extrai o timestamp da linha
     */
    private function extractTimestamp(string $line): ?string
    {
        preg_match('/\[(.*?)\]/', $line, $matches);
        return $matches[1] ?? null;
    }
}
