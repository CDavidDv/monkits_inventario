<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemItems;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // Page
    // =========================================================================

    public function index()
    {
        $categories = Category::orderBy('name')->get(['id', 'name', 'type']);

        return Inertia::render('Items/Import', [
            'categories' => $categories,
        ]);
    }

    // =========================================================================
    // Template download (.xlsx)
    // =========================================================================

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Items');

        // Header row
        $headers = array(
            'A' => 'ID (modelo)',
            'B' => 'Tipo',
            'C' => 'Nombre',
            'D' => 'Categoría',
            'E' => 'Stock Actual',
            'F' => 'Stock Mínimo',
            'G' => 'Stock Máximo',
            'H' => 'Costo de Compra',
            'I' => 'Precio de Venta',
            'J' => 'Unidad de Medida',
            'K' => 'Activo',
            'L' => 'Código de Barras',
            'M' => 'Imagen',
            'N' => 'Descripción',
            'O' => 'Ubicación',
            'P' => 'Ítems Asignados',
        );

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
        }

        // Header styling
        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => '1F4E79')),
            'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
        );
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

        // Example row
        $example = array(
            'MOD001', 'Elementos individuales', 'Resistencia 1K', 'Electrónica',
            '100', '10', '500',
            '', '',
            'Pieza', 'SI', 'RES-1K-001', '', 'Resistencia de carbón 1KΩ 1/4W', 'Estante A-01', '',
        );
        $cols = range('A', 'P');
        foreach ($cols as $i => $col) {
            $sheet->setCellValue($col . '2', isset($example[$i]) ? $example[$i] : '');
        }

        // Example row 2 (kit with assigned items)
        $example2 = array(
            'KIT001', 'Kits', 'Kit Arduino Básico', 'Kits Electrónica',
            '5', '2', '20',
            '', '',
            'Unidad', 'SI', '', '', 'Kit básico para iniciarse en Arduino', 'Estante B-05', '3:MOD001,2:MOD002',
        );
        foreach ($cols as $i => $col) {
            $sheet->setCellValue($col . '3', isset($example2[$i]) ? $example2[$i] : '');
        }

        // Auto-width columns
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Instructions sheet
        $infoSheet = $spreadsheet->createSheet();
        $infoSheet->setTitle('Instrucciones');
        $instructions = array(
            array('Campo', 'Descripción', 'Requerido', 'Valores válidos'),
            array('ID (modelo)', 'Identificador único del item. Si ya existe, se actualiza completamente.', 'No', 'Texto libre (MOD001, etc.)'),
            array('Tipo', 'Tipo de item en español', 'Sí', 'individual/elemento, componente/componentes, kit'),
            array('Nombre', 'Nombre del item', 'Sí', 'Texto libre'),
            array('Categoría', 'Nombre de la categoría. Se crea automáticamente si no existe.', 'No', 'Texto libre'),
            array('Stock Actual', 'Cantidad actual en inventario', 'No', 'Número (default: 0)'),
            array('Stock Mínimo', 'Stock mínimo para alertas', 'No', 'Número (default: 0)'),
            array('Stock Máximo', 'Stock máximo permitido', 'No', 'Número (default: 0)'),
            array('Costo de Compra', 'Se ignora en la importación', 'No', '(ignorado)'),
            array('Precio de Venta', 'Se ignora en la importación', 'No', '(ignorado)'),
            array('Unidad de Medida', 'Unidad del item', 'No', 'Pieza, Kg, Litro, etc.'),
            array('Activo', 'Si el item está activo', 'No', 'SI / NO (default: SI)'),
            array('Código de Barras', 'Código de barras único', 'No', 'Texto libre'),
            array('Imagen', 'Nombre de archivo. Si está vacío, se asigna automáticamente: modelo.webp', 'No', 'MOD001.jpg, MOD001.png — o dejar vacío'),
            array('Descripción', 'Descripción detallada del item', 'No', 'Texto libre'),
            array('Ubicación', 'Ubicación en almacén o bodega', 'No', 'Texto libre (ej: Estante A-01)'),
            array('Ítems Asignados', 'Componentes del kit en formato cantidad:modelo', 'No', '5:MOD001,3:MOD002'),
        );
        foreach ($instructions as $rowNum => $row) {
            $infoSheet->fromArray($row, null, 'A' . ($rowNum + 1));
        }
        $infoSheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        foreach (range('A', 'D') as $col) {
            $infoSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, array(
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_items.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ));
    }

    // =========================================================================
    // Import (.xlsx or .csv)
    // =========================================================================

    public function import(Request $request)
    {
        $request->validate(array(
            'archivo' => 'required|file|max:10240',
        ), array(
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.max'      => 'El archivo no debe superar 10MB.',
        ));

        $file     = $request->file('archivo');
        $ext      = strtolower($file->getClientOriginalExtension());
        $allowed  = array('xlsx', 'xls', 'csv');

        if (!in_array($ext, $allowed)) {
            return back()->withErrors(array('archivo' => 'Solo se permiten archivos .xlsx, .xls o .csv.'));
        }

        // Parse file into rows array
        try {
            $rows = $this->parseFile($file->getRealPath(), $ext);
        } catch (\Exception $e) {
            return back()->withErrors(array('archivo' => 'Error al leer el archivo: ' . $e->getMessage()));
        }

        if (count($rows) < 2) {
            return back()->withErrors(array('archivo' => 'El archivo está vacío o solo tiene encabezados.'));
        }

        // Normalize headers (trim, lowercase)
        $rawHeaders = array_shift($rows);
        $headerMap  = $this->buildHeaderMap($rawHeaders);

        $errors   = array();
        $created  = 0;
        $updated  = 0;

        // Two-pass: pass 1 = upsert items, pass 2 = relationships
        $relationshipQueue = array(); // [ ['parent_modelo' => ..., 'raw' => '5:MOD001,3:MOD002'], ... ]

        DB::beginTransaction();

        try {
            foreach ($rows as $lineNum => $row) {
                $rowNum = $lineNum + 2;

                // Pad row to header count
                $row  = array_pad((array) $row, count($headerMap), null);
                $data = array();
                foreach ($headerMap as $field => $colIndex) {
                    $data[$field] = isset($row[$colIndex]) ? trim((string) $row[$colIndex]) : '';
                }

                // Skip blank rows
                if (empty($data['nombre']) && empty($data['tipo'])) {
                    continue;
                }

                // --- Validate required fields ---
                $rowErrors = array();

                $nombre = isset($data['nombre']) ? $data['nombre'] : '';
                $tipoRaw = strtolower(isset($data['tipo']) ? $data['tipo'] : '');
                $type    = $this->mapType($tipoRaw);

                if (empty($nombre))  $rowErrors[] = 'Nombre es requerido';
                if ($type === null)  $rowErrors[] = "Tipo inválido '{$tipoRaw}' (usa: individual/elemento, componente/componentes, kit)";

                if (!empty($rowErrors)) {
                    $errors[] = "Fila {$rowNum}: " . implode(', ', $rowErrors);
                    continue;
                }

                // --- Resolve category ---
                $categoryId = null;
                $catName    = isset($data['categoria']) ? $data['categoria'] : '';
                if (!empty($catName)) {
                    $categoryId = $this->resolveCategory($catName, $type);
                }

                // --- Stock fields ---
                $currentStock = is_numeric(isset($data['stock_actual']) ? $data['stock_actual'] : '') ? (float) $data['stock_actual'] : 0;
                $minStock     = is_numeric(isset($data['stock_minimo']) ? $data['stock_minimo'] : '') ? (float) $data['stock_minimo']  : 0;
                $maxStock     = is_numeric(isset($data['stock_maximo']) ? $data['stock_maximo'] : '') ? (float) $data['stock_maximo']  : 0;

                // --- Active flag ---
                $activeRaw = strtolower(trim(isset($data['activo']) ? $data['activo'] : 'si'));
                $active    = !in_array($activeRaw, array('no', '0', 'false', 'n'));

                // --- modelo ---
                $modelo = !empty(isset($data['modelo']) ? $data['modelo'] : '') ? $data['modelo'] : null;

                // --- Image auto-detection: if empty, use modelo.webp ---
                $imagen = isset($data['imagen']) ? $data['imagen'] : '';
                if (empty($imagen) && $modelo !== null) {
                    $imagen = $modelo . '.webp';
                }

                // --- Description and Location (optional) ---
                $descripcion = isset($data['descripcion']) ? $data['descripcion'] : '';
                $ubicacion = isset($data['ubicacion']) ? $data['ubicacion'] : '';

                // --- Upsert logic ---
                $existingItem = $modelo ? Item::where('modelo', $modelo)->first() : null;

                if ($existingItem) {
                    // UPDATE: update all fields
                    $quantityBefore = $existingItem->current_stock;

                    $updateData = array(
                        'name'          => $nombre,
                        'type'          => $type,
                        'category_id'   => $categoryId,
                        'unit'          => !empty(isset($data['unidad']) ? $data['unidad'] : '') ? $data['unidad'] : 'unidad',
                        'current_stock' => $currentStock,
                        'min_stock'     => $minStock,
                        'max_stock'     => $maxStock,
                        'codigo_barras' => !empty(isset($data['codigo_barras']) ? $data['codigo_barras'] : '') ? $data['codigo_barras'] : null,
                        'imagenes'      => !empty($imagen) ? $imagen : null,
                        'active'        => $active,
                        'description'   => !empty($descripcion) ? $descripcion : null,
                        'location'      => !empty($ubicacion) ? $ubicacion : null,
                    );
                    $existingItem->update($updateData);

                    // Registrar movimiento de inventario si el stock cambió
                    if ($quantityBefore != $currentStock) {
                        $quantityDiff = $currentStock - $quantityBefore;
                        InventoryMovement::create(array(
                            'component_id'    => $existingItem->id,
                            'type'            => $quantityDiff > 0 ? 'in' : 'out',
                            'concept'         => 'Ajuste por importación',
                            'quantity'        => abs($quantityDiff),
                            'quantity_before' => $quantityBefore,
                            'quantity_after'  => $currentStock,
                            'movement_date'   => now(),
                            'created_by'      => Auth::id(),
                            'reference'       => 'import',
                            'notes'           => 'Importación de items - ' . $nombre,
                        ));
                    }
                    $updated++;
                } else {
                    // CREATE
                    $newItem = Item::create(array(
                        'modelo'        => $modelo,
                        'name'          => $nombre,
                        'type'          => $type,
                        'category_id'   => $categoryId,
                        'unit'          => !empty(isset($data['unidad']) ? $data['unidad'] : '') ? $data['unidad'] : 'unidad',
                        'current_stock' => $currentStock,
                        'min_stock'     => $minStock,
                        'max_stock'     => $maxStock,
                        'purchase_cost' => null,
                        'sale_price'    => null,
                        'codigo_barras' => !empty(isset($data['codigo_barras']) ? $data['codigo_barras'] : '') ? $data['codigo_barras'] : null,
                        'imagenes'      => !empty($imagen) ? $imagen : null,
                        'active'        => $active,
                        'description'   => !empty($descripcion) ? $descripcion : null,
                        'location'      => !empty($ubicacion) ? $ubicacion : null,
                    ));

                    // Registrar movimiento de inventario si hay stock inicial
                    if ($currentStock > 0) {
                        InventoryMovement::create(array(
                            'component_id'    => $newItem->id,
                            'type'            => 'in',
                            'concept'         => 'Carga por importación',
                            'quantity'        => $currentStock,
                            'quantity_before' => 0,
                            'quantity_after'  => $currentStock,
                            'movement_date'   => now(),
                            'created_by'      => Auth::id(),
                            'reference'       => 'import',
                            'notes'           => 'Importación de items - ' . $nombre,
                        ));
                    }
                    $created++;
                }

                // Queue relationship processing (pass 2)
                $asignados = isset($data['items_asignados']) ? $data['items_asignados'] : '';
                if (!empty($asignados) && $modelo !== null) {
                    $relationshipQueue[] = array(
                        'parent_modelo' => $modelo,
                        'raw'           => $asignados,
                        'row_num'       => $rowNum,
                    );
                }
            }

            // --- Pass 2: process relationships ---
            foreach ($relationshipQueue as $entry) {
                $parent = Item::where('modelo', $entry['parent_modelo'])->first();
                if (!$parent) continue;

                // Delete existing assignments for this parent
                ItemItems::where('item_id', $parent->id)->delete();

                $pairs = explode(',', $entry['raw']);
                foreach ($pairs as $pair) {
                    $pair = trim($pair);
                    if (!preg_match('/^(\d+):(.+)$/', $pair, $m)) {
                        $errors[] = "Fila " . $entry['row_num'] . ": formato inválido en ítems asignados '{$pair}' (usa qty:modelo)";
                        continue;
                    }
                    $qty          = (int) $m[1];
                    $childModelo  = trim($m[2]);
                    $child        = Item::where('modelo', $childModelo)->first();
                    if (!$child) {
                        $errors[] = "Fila " . $entry['row_num'] . ": item '{$childModelo}' no encontrado para asignación";
                        continue;
                    }
                    ItemItems::create(array(
                        'item_id'   => $parent->id,
                        'item_id_2' => $child->id,
                        'quantity'  => $qty,
                    ));
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(array('archivo' => 'Error al importar: ' . $e->getMessage()));
        }

        $message = "Importación completada: {$created} creados, {$updated} actualizados.";
        if (!empty($errors)) {
            $message .= ' Con advertencias en ' . count($errors) . ' filas.';
        }

        return back()->with(array(
            'success'         => $message,
            'import_errors'   => $errors,
            'imported_count'  => $created,
            'updated_count'   => $updated,
        ));
    }

    // =========================================================================
    // Clear all items
    // =========================================================================

    public function clearAll(Request $request)
    {
        DB::beginTransaction();
        try {
            // Eliminar relaciones entre items primero
            \App\Models\ItemItems::query()->delete();
            // Eliminar movimientos de inventario
            InventoryMovement::query()->delete();
            // Eliminar todos los items
            Item::query()->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(array('clear' => 'Error al vaciar la base de datos: ' . $e->getMessage()));
        }

        return back()->with('clear_success', 'Base de datos de items vaciada correctamente.');
    }

    // =========================================================================
    // Export (.xlsx)
    // =========================================================================

    public function export()
    {
        $items = Item::with(array('category', 'assignedItems.component'))->get();

        // Registrar movimiento de auditoría: exportación
        InventoryMovement::create(array(
            'type'            => 'adjustment',
            'concept'         => 'Exportación de inventario',
            'quantity'        => 0,
            'movement_date'   => now(),
            'created_by'      => Auth::id(),
            'reference'       => 'export',
            'notes'           => 'Exportación de todos los items a Excel - ' . count($items) . ' items',
        ));

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Items');

        // Headers
        $headers = array(
            'ID (modelo)', 'Tipo', 'Nombre', 'Categoría',
            'Stock Actual', 'Stock Mínimo', 'Stock Máximo',
            'Costo de Compra', 'Precio de Venta', 'Unidad de Medida',
            'Activo', 'Código de Barras', 'Imagen', 'Descripción', 'Ubicación', 'Ítems Asignados',
        );
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => '1F4E79')),
            'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
        );
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        foreach ($items as $item) {
            // Build items_asignados string
            $asignadosStr = '';
            if ($item->assignedItems && count($item->assignedItems) > 0) {
                $parts = array();
                foreach ($item->assignedItems as $rel) {
                    if ($rel->component && $rel->component->modelo) {
                        $parts[] = $rel->quantity . ':' . $rel->component->modelo;
                    }
                }
                $asignadosStr = implode(',', $parts);
            }

            $sheet->fromArray(array(
                $item->modelo !== null ? $item->modelo : '',
                $this->reverseMapType($item->type),
                $item->name,
                $item->category ? $item->category->name : '',
                (float) $item->current_stock,
                (float) $item->min_stock,
                (float) $item->max_stock,
                $item->purchase_cost !== null ? (float) $item->purchase_cost : '',
                $item->sale_price !== null ? (float) $item->sale_price : '',
                $item->unit !== null ? $item->unit : '',
                $item->active ? 'SI' : 'NO',
                $item->codigo_barras !== null ? $item->codigo_barras : '',
                $item->imagenes !== null ? $item->imagenes : '',
                $item->description !== null ? $item->description : '',
                $item->location !== null ? $item->location : '',
                $asignadosStr,
            ), null, "A{$row}");

            $row++;
        }

        // Auto-width
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'inventario_' . date('Y-m-d') . '.xlsx';

        return response($content, 200, array(
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ));
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Parse xlsx/xls/csv into a 2D array (rows of cells).
     */
    private function parseFile($path, $ext)
    {
        if ($ext === 'csv') {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            return array_map('str_getcsv', $lines);
        }

        // xlsx / xls via PhpSpreadsheet
        $readerType  = $ext === 'xls' ? 'Xls' : 'Xlsx';
        $reader      = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $rows = array();
        foreach ($sheet->getRowIterator() as $sheetRow) {
            $cells = array();
            foreach ($sheetRow->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }
            // Skip entirely empty rows
            $hasContent = false;
            foreach ($cells as $v) {
                if ($v !== null && $v !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) {
                continue;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * Build a map of [fieldKey => columnIndex] from the raw header row.
     * Uses loose Spanish matching to be tolerant of minor differences.
     */
    private function buildHeaderMap(array $rawHeaders)
    {
        $normalized = array();
        foreach ($rawHeaders as $h) {
            $normalized[] = mb_strtolower(trim((string) $h));
        }

        // Map expected header strings to internal field keys
        $mappings = array(
            'id (modelo)'        => 'modelo',
            'modelo'             => 'modelo',
            'tipo'               => 'tipo',
            'nombre'             => 'nombre',
            'categoría'          => 'categoria',
            'categoria'          => 'categoria',
            'stock actual'       => 'stock_actual',
            'stock mínimo'       => 'stock_minimo',
            'stock minimo'       => 'stock_minimo',
            'stock máximo'       => 'stock_maximo',
            'stock maximo'       => 'stock_maximo',
            'costo de compra'    => 'costo_compra',
            'precio de venta'    => 'precio_venta',
            'unidad de medida'   => 'unidad',
            'unidad'             => 'unidad',
            'activo'             => 'activo',
            'código de barras'   => 'codigo_barras',
            'codigo de barras'   => 'codigo_barras',
            'imagen'             => 'imagen',
            'imagenes'           => 'imagen',
            'descripción'        => 'descripcion',
            'descripcion'        => 'descripcion',
            'ubicación'          => 'ubicacion',
            'ubicacion'          => 'ubicacion',
            'ítems asignados'    => 'items_asignados',
            'items asignados'    => 'items_asignados',
        );

        $map = array();
        foreach ($normalized as $index => $header) {
            if (isset($mappings[$header])) {
                $map[$mappings[$header]] = $index;
            }
        }

        return $map;
    }

    /**
     * Map Spanish type name from Excel to DB enum value.
     * Returns null if unrecognized.
     */
    private function mapType($raw)
    {
        $map = array(
            'individual'           => 'element',
            'element'              => 'element',
            'elemento'             => 'element',
            'elementos individuales' => 'element',
            'elemento individual'  => 'element',
            'componente'           => 'component',
            'component'            => 'component',
            'componentes'          => 'component',
            'kit'                  => 'kit',
            'kits'                 => 'kit',
        );
        return isset($map[$raw]) ? $map[$raw] : null;
    }

    /**
     * Reverse-map DB enum to Spanish Excel value for export.
     */
    private function reverseMapType($type)
    {
        $map = array(
            'element'   => 'individual',
            'component' => 'componente',
            'kit'       => 'kit',
        );
        return isset($map[$type]) ? $map[$type] : $type;
    }

    /**
     * Look up or create a category by name.
     * NOTE: the category table has a UNIQUE constraint on `name` only,
     * so we use firstOrCreate by name alone. The `type` is set on create.
     */
    private function resolveCategory($name, $itemType)
    {
        $existing = Category::whereRaw('LOWER(name) = ?', array(mb_strtolower($name)))->first();

        if ($existing) {
            return $existing->id;
        }

        $category = Category::create(array(
            'name'       => $name,
            'type'       => $itemType,
            'color'      => '#6B7280',
            'active'     => true,
            'created_by' => Auth::id(),
        ));

        return $category->id;
    }

    /**
     * Auto-detect image file in public/images/items/ matching modelo.{ext}.
     * Returns the filename if found, or empty string.
     */
    private function detectImage($modelo)
    {
        $basePath   = public_path('images/items/');
        $extensions = array('webp', 'png', 'jpg', 'jpeg', 'gif');

        foreach ($extensions as $ext) {
            $filename = $modelo . '.' . $ext;
            if (file_exists($basePath . $filename)) {
                return $filename;
            }
        }

        return '';
    }
}
