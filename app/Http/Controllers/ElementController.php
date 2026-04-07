<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ElementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Item::where('type', 'element')
            ->with(['category', 'stockAlerts']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        if ($request->category) {
            $cat = $request->category;
            $query->whereHas('category', function ($q) use ($cat) {
                $q->where('name', $cat);
            });
        }

        if ($request->stock_status) {
            if ($request->stock_status === 'low') {
                $query->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock');
            } elseif ($request->stock_status === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->stock_status === 'over') {
                $query->whereColumn('current_stock', '>=', 'max_stock')->where('max_stock', '>', 0);
            }
        }

        $sort      = in_array($request->sort, ['name', 'current_stock']) ? $request->sort : 'name';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $direction);

        $elements = $query->paginate(20)->through(function ($item) {
            return [
                'id'            => $item->id,
                'name'          => $item->name,
                'description'   => $item->description,
                'category'      => $item->category ? $item->category->name : null,
                'current_stock' => $item->current_stock,
                'min_stock'     => $item->min_stock,
                'max_stock'     => $item->max_stock,
                'unit'          => $item->unit,
                'location'      => $item->location,
                'active'        => $item->active,
                'stock_alerts'  => $item->stockAlerts ? $item->stockAlerts->toArray() : [],
            ];
        })->appends($request->query());

        $categories = Item::where('type', 'element')
            ->with('category')
            ->get()
            ->pluck('category.name')
            ->filter()
            ->unique()
            ->values();

        $stats = [
            'total'         => Item::where('type', 'element')->count(),
            'low_stock'     => Item::where('type', 'element')
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<=', 'min_stock')
                ->count(),
            'out_of_stock'  => Item::where('type', 'element')
                ->where('current_stock', '<=', 0)
                ->count(),
            'over_stock'    => Item::where('type', 'element')
                ->whereColumn('current_stock', '>=', 'max_stock')
                ->where('max_stock', '>', 0)
                ->count(),
        ];

        return Inertia::render('Elements/Index', [
            'elements'   => $elements,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category', 'stock_status', 'sort', 'direction']),
            'stats'      => $stats,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Elements/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'codigo_barras' => 'nullable|string|max:255',
            'imagenes' => 'nullable|string',
        ]);

        $element = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'component',
            'category_id' => $request->category_id,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'current_stock' => $request->current_stock ?? 0,
            'purchase_cost' => $request->purchase_cost,
            'sale_price' => $request->sale_price,
            'location' => $request->location,
            'codigo_barras' => $request->codigo_barras,
            'imagenes' => $request->imagenes,
            'active' => true
        ]);

        return redirect()->route('elements.index')
            ->with('success', 'Elemento creado exitosamente');
    }

    public function show(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->load('category');

        return Inertia::render('Elements/Show', [
            'element' => $element
        ]);
    }

    public function edit(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->load('category');

        $categories = Category::all();


        return Inertia::render('Elements/Edit', [
            'element' => $element,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'codigo_barras' => 'nullable|string|max:255',
            'imagenes' => 'nullable|string',
        ]);

        $element->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'purchase_cost' => $request->purchase_cost,
            'sale_price' => $request->sale_price,
            'location' => $request->location,
            'codigo_barras' => $request->codigo_barras,
            'imagenes' => $request->imagenes,
        ]);

        return redirect()->route('elements.index')
            ->with('success', 'Elemento actualizado exitosamente');
    }

    public function destroy(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->delete();

        return redirect()->route('elements.index')
            ->with('success', 'Elemento eliminado exitosamente');
    }

    public function updateStock(Request $request, Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out,adjustment',
            'notes' => 'nullable|string'
        ]);

        $oldStock = $element->current_stock;
        
        switch ($request->type) {
            case 'in':
                $newStock = $oldStock + $request->quantity;
                break;
            case 'out':
                $newStock = $oldStock - $request->quantity;
                if ($newStock < 0) {
                    return back()->withErrors(['error' => 'No hay suficiente stock disponible']);
                }
                break;
            case 'adjustment':
                $newStock = $request->quantity;
                break;
        }

        $element->update(['current_stock' => $newStock]);

        return back()->with('success', 'Stock actualizado exitosamente');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $elements = Item::where('type', 'component')
            ->where('active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json($elements);
    }

    public function dashboardStats()
    {
        $stats = [
            'total_elements' => Item::where('type', 'component')->where('active', true)->count(),
            'low_stock_elements' => Item::where('type', 'component')
                ->where('active', true)
                ->where('current_stock', '<=', 'min_stock')
                ->count(),
            'over_stock_elements' => Item::where('type', 'component')
                ->where('active', true)
                ->where('current_stock', '>=', 'max_stock')
                ->count(),
            'total_value' => Item::where('type', 'component')
                ->where('active', true)
                ->sum(DB::raw('current_stock * purchase_cost'))
        ];

        return response()->json($stats);
    }
}
