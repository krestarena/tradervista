## Resolving `routes/admin.php` conflicts

When merging branches that both touch `routes/admin.php`, Git may flag a conflict
around the TradeVista imports and routes. Preserve the TradeVista additions so
admin reporting and commission-plan tools keep working.

Steps to resolve:

1) **Keep the TradeVista import.** At the top of the file, ensure the `use` line
   for the report controller remains:

   ```php
   use App\Http\Controllers\Admin\TradeVista\ReportController as TradeVistaReportController;
   ```

2) **Keep the TradeVista route block.** Inside the `/admin` route group, retain
   the block labeled `// TradeVista tools` that registers commission-plan CRUD
   and the voucher-liability/commission-hold exports:

   ```php
   // TradeVista tools
   Route::resource('tradevista/commission-plans', CommissionPlanController::class)->names([...]);
   Route::controller(TradeVistaReportController::class)
       ->prefix('tradevista/reports')
       ->name('admin.tradevista.reports.')
       ->group(function () {
           Route::get('/', 'index')->name('index');
           Route::get('/voucher-liability', 'exportVoucherLiability')->name('voucher-liability');
           Route::get('/commission-holds', 'exportCommissionHoldQueue')->name('commission-holds');
       });
   ```

3) Remove any conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`) and save. Then
   run a quick syntax check:

   ```bash
   php -l routes/admin.php
   ```

If your editor offers "Accept current/incoming/both" options, pick **Accept
both changes** so the TradeVista import and route block stay alongside the
existing admin routes before you clean up the markers and whitespace.

Committing with this merged block will unblock the branch merge.
