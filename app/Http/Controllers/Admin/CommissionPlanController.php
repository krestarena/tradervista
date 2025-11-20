<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CommissionPlan;
use Illuminate\Http\Request;

class CommissionPlanController extends Controller
{
    public function index()
    {
        $plans = CommissionPlan::with('categories')->latest()->paginate(20);

        return view('backend.tradevista.commission_plans.index', compact('plans'));
    }

    public function create()
    {
        $categories = Category::all();
        $plan = new CommissionPlan();

        return view('backend.tradevista.commission_plans.form', compact('plan', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $plan = CommissionPlan::create($data);
        $plan->categories()->sync($request->input('category_ids', []));

        flash(translate('Commission plan created.'))->success();

        return redirect()->route('admin.commission-plans.index');
    }

    public function edit(CommissionPlan $commissionPlan)
    {
        $categories = Category::all();

        return view('backend.tradevista.commission_plans.form', [
            'plan' => $commissionPlan,
            'categories' => $categories,
        ]);
    }

    public function show(CommissionPlan $commissionPlan)
    {
        return redirect()->route('admin.commission-plans.edit', $commissionPlan);
    }

    public function update(Request $request, CommissionPlan $commissionPlan)
    {
        $data = $this->validatedData($request);

        $commissionPlan->update($data);
        $commissionPlan->categories()->sync($request->input('category_ids', []));

        flash(translate('Commission plan updated.'))->success();

        return redirect()->route('admin.commission-plans.index');
    }

    public function destroy(CommissionPlan $commissionPlan)
    {
        $commissionPlan->categories()->detach();
        $commissionPlan->delete();

        flash(translate('Commission plan removed.'))->success();

        return redirect()->route('admin.commission-plans.index');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'notes' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);
    }
}
