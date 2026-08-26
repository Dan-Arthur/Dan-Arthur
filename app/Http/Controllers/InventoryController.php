<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    // ============================================================
    // STOCK ITEMS
    // ============================================================

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('view inventory'), 403);

        $schoolId = auth()->user()->school_id;
        $tab = $request->get('tab', 'items');

        $query = InventoryItem::where('school_id', $schoolId)->with('category');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('name', 'like', $s)->orWhere('code', 'like', $s));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->get('stock') === 'low') {
            $query->where('quantity_in_stock', '>', 0)->whereColumn('quantity_in_stock', '<=', 'reorder_level');
        } elseif ($request->get('stock') === 'out') {
            $query->where('quantity_in_stock', 0);
        }

        $items         = $query->orderBy('name')->paginate(30)->withQueryString();
        $categories    = AssetCategory::where('school_id', $schoolId)->orderBy('name')->get();
        $totalItems    = InventoryItem::where('school_id', $schoolId)->count();
        $lowStockCount = InventoryItem::where('school_id', $schoolId)
            ->where('quantity_in_stock', '>', 0)
            ->whereColumn('quantity_in_stock', '<=', 'reorder_level')->count();
        $outOfStockCount = InventoryItem::where('school_id', $schoolId)
            ->where('quantity_in_stock', 0)->count();

        return view('inventory.index', compact('items', 'categories', 'totalItems', 'lowStockCount', 'outOfStockCount'));
    }

    public function storeItem(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage inventory'), 403);

        $validated = $request->validate([
            'name'          => 'required|string|max:200',
            'code'          => 'nullable|string|max:50',
            'unit'          => 'required|in:' . implode(',', array_keys(InventoryItem::UNITS)),
            'category_id'   => 'nullable|exists:asset_categories,id',
            'opening_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_cost'     => 'nullable|numeric|min:0',
        ]);

        $opening = $validated['opening_stock'];
        unset($validated['opening_stock']);

        InventoryItem::create(array_merge($validated, [
            'school_id'         => auth()->user()->school_id,
            'quantity_in_stock' => $opening,
            'is_active'         => true,
        ]));

        return redirect()->route('inventory.index')->with('success', 'Item added.');
    }

    public function showItem(InventoryItem $item): View
    {
        abort_unless(auth()->user()->can('view inventory'), 403);
        abort_unless($item->school_id == auth()->user()->school_id, 403);

        $item->load('category');
        $movements = $item->movements()->with('recordedBy', 'supplier')->orderByDesc('movement_date')->paginate(20);
        $suppliers = Supplier::where('school_id', auth()->user()->school_id)->where('is_active', true)->get();

        return view('inventory.show-item', compact('item', 'movements', 'suppliers'));
    }

    public function recordMovement(Request $request, InventoryItem $item): RedirectResponse
    {
        abort_unless(auth()->user()->can('record stock movements'), 403);
        abort_unless($item->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'movement_type' => 'required|in:' . implode(',', array_keys(StockMovement::TYPES)),
            'quantity'      => 'required|integer|min:1',
            'unit_cost'     => 'nullable|numeric|min:0',
            'movement_date' => 'nullable|date',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'reference'     => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $item) {
            $qty = $validated['quantity'];
            $delta = in_array($validated['movement_type'], ['purchase', 'return']) ? $qty : -$qty;
            $newBalance = max(0, $item->quantity_in_stock + $delta);

            StockMovement::create([
                'school_id'         => $item->school_id,
                'inventory_item_id' => $item->id,
                'movement_type'     => $validated['movement_type'],
                'quantity'          => $delta,
                'balance_after'     => $newBalance,
                'unit_cost'         => $validated['unit_cost'] ?? null,
                'movement_date'     => $validated['movement_date'] ?? now()->toDateString(),
                'supplier_id'       => $validated['supplier_id'] ?? null,
                'reference'         => $validated['reference'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'recorded_by'       => auth()->id(),
            ]);

            $item->update(['quantity_in_stock' => $newBalance]);
        });

        return back()->with('success', 'Stock movement recorded.');
    }

    // ============================================================
    // ASSETS
    // ============================================================

    public function assets(Request $request): View
    {
        abort_unless(auth()->user()->can('view inventory'), 403);

        $schoolId = auth()->user()->school_id;

        $query = Asset::where('school_id', $schoolId)->with(['category', 'supplier']);

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('name', 'like', $s)
                ->orWhere('asset_tag', 'like', $s)
                ->orWhere('serial_number', 'like', $s));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $assets     = $query->orderBy('name')->paginate(30)->withQueryString();
        $categories = AssetCategory::where('school_id', $schoolId)->orderBy('name')->get();
        $suppliers  = Supplier::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $statuses   = Asset::STATUSES;

        return view('inventory.assets', compact('assets', 'categories', 'suppliers', 'statuses'));
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage assets'), 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:200',
            'asset_tag'       => 'nullable|string|max:50',
            'serial_number'   => 'nullable|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'model'           => 'nullable|string|max:100',
            'category_id'     => 'nullable|exists:asset_categories,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'purchase_price'  => 'nullable|numeric|min:0',
            'purchase_date'   => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location'        => 'nullable|string|max:200',
            'condition'       => 'required|in:' . implode(',', array_keys(Asset::CONDITIONS)),
        ]);

        Asset::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
            'status'    => 'active',
        ]));

        return redirect()->route('inventory.assets')->with('success', 'Asset registered.');
    }

    public function updateAsset(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage assets'), 403);
        abort_unless($asset->school_id == auth()->user()->school_id, 403);

        $validated = $request->validate([
            'status'    => 'required|in:' . implode(',', array_keys(Asset::STATUSES)),
            'condition' => 'required|in:' . implode(',', array_keys(Asset::CONDITIONS)),
            'location'  => 'nullable|string|max:200',
        ]);

        $asset->update($validated);

        return back()->with('success', 'Asset updated.');
    }

    // ============================================================
    // SUPPLIERS + CATEGORIES (shared catalogue)
    // ============================================================

    public function catalogue(Request $request): View
    {
        abort_unless(auth()->user()->can('manage inventory'), 403);

        $schoolId   = auth()->user()->school_id;
        $categories = AssetCategory::where('school_id', $schoolId)->withCount(['assets', 'inventoryItems'])->orderBy('name')->get();
        $suppliers  = Supplier::where('school_id', $schoolId)->withCount(['assets', 'stockMovements'])->orderBy('name')->get();

        return view('inventory.catalogue', compact('categories', 'suppliers'));
    }

    public function storeCatalogue(Request $request): RedirectResponse
    {
        return match ($request->input('type')) {
            'supplier' => $this->storeSupplier($request),
            default    => $this->storeCategory($request),
        };
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage inventory'), 403);

        $validated = $request->validate(['name' => 'required|string|max:150', 'code' => 'nullable|string|max:30']);
        AssetCategory::create(array_merge($validated, ['school_id' => auth()->user()->school_id]));

        return redirect()->route('inventory.catalogue')->with('success', 'Category added.');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage inventory'), 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:150',
        ]);

        Supplier::create(array_merge($validated, ['school_id' => auth()->user()->school_id, 'is_active' => true]));

        return redirect()->route('inventory.catalogue')->with('success', 'Supplier added.');
    }
}
