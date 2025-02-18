<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    /* START OF SHOP ENDPOINTS */
    $router->resource('product-categories', ProductCategoryController::class);

    /* END OF SHOP ENDPOINTS */

    $router->get('/', 'HomeController@index')->name('home');
    $router->get('vaccination-stats', 'HomeController@vaccination')->name('vaccination-stats');
    $router->get('maaf-application-forms', 'HomeController@maaf_application_forms')->name('maaf-application-forms');
    $router->get('/become-farmer', 'HomeController@become_farmer');
    $router->resource('product-orders', ProductOrderController::class);
    $router->resource('requests', VendorController::class);
    $router->resource('districts', DistrictController::class);
    $router->resource('sub-counties', SubCountyController::class);
    $router->resource('farms', FarmController::class);
    $router->resource('animals', AnimalController::class);
    $router->resource('events', EventController::class);
    $router->resource('movements', MovementController::class);
    $router->resource('diseases', DiseaseController::class);
    $router->resource('medicines', MedicineController::class);
    $router->resource('vaccines', VaccineController::class);
    $router->resource('movement-items', MovementsItemsController::class);
    $router->resource('slaughter-records', SlaughterRecordController::class);
    $router->resource('archived-animals', ArchivedAnimalController::class);
    $router->resource('sales', AnimalSalesController::class);
    $router->resource('check-points', CheckPointController::class);
    $router->resource('movement-routes', MovementRouteController::class);
    $router->resource('check-point-records', CheckPointRecordController::class);
    //$router->resource('product-categories', ProductCategoryController::class);
    $router->resource('products', ProductController::class);
    $router->resource('my-products', MyProductController::class);
    $router->resource('drugs', DrugBatchController::class);
    $router->resource('orders', OrderController::class);
    $router->resource('drug-categories', DrugCategoryController::class);
    $router->resource('form-drug-sellers', FormDrugSellerController::class);
    $router->resource('form-drug-stock-approvals', FormDrugStockApprovalController::class);
    $router->resource('drug-stock-batches', DrugStockBatchController::class);
    $router->resource('drug-stock-batch-records', DrugStockBatchRecordController::class);
    $router->resource('animals-1', Animal1Controller::class);
    $router->resource('sick-animals', SickAnimalController::class);
    $router->resource('pregnant-animals', PregnantAnimalController::class);
    $router->resource('finance-categories', FinanceCategoryController::class);
    $router->resource('transactions', TransactionController::class);
    $router->resource('milk', MilkController::class);
    $router->resource('slaughter-houses', SlaughterHouseController::class);
    $router->resource('admin-role-users', AdminRoleUserController::class);
    $router->resource('checkpoint-sessions', CheckpointSessionController::class);
    $router->resource('vet-service-categories', VetServiceCategoryController::class);
    $router->resource('locations', LocationController::class);
    $router->resource('sessions', BatchSessionController::class);

    $router->resource('groups', GroupController::class);
    $router->resource('gens', GenController::class);
    $router->resource('trips', TripController::class);

    $router->resource('wholesale-drug-stocks', WholesaleDrugStockController::class);
    $router->resource('wholesale-orders', WholesaleOrderController::class);
    $router->resource('wholesale-order-items', WholesaleOrderItemController::class);
    $router->resource('notifications', NotificationModelController::class);
    $router->resource('treatment-events', TreatmentEventController::class);

    $router->resource('main-vaccine-stocks', VaccineMainStockController::class);
    $router->resource('vaccine-categories', VaccineCategoryController::class);
    $router->resource('district-vaccine-stocks', DistrictVaccineStockController::class);
    $router->resource('vaccine-events', VaccineEventController::class);
    $router->resource('vaccination-schedules', VaccinationScheduleController::class);
    $router->resource('vaccination-programs', VaccinationProgramController::class);

    $router->resource('farm-vaccination-records', FarmVaccinationRecordController::class);
    $router->get('maps', 'MapController@index')->name('maps');
    $router->get('charts', 'MapController@charts')->name('charts');
    $router->resource('farm-reports', FarmReportController::class);

    $router->resource('application-types', ApplicationTypeController::class);
    $router->resource('pending-applications', ApplicationController::class);
    $router->resource('inspection-applications', ApplicationController::class);
    $router->resource('payment-applications', ApplicationController::class);
    $router->resource('approved-applications', ApplicationController::class);
    $router->resource('applications', ApplicationController::class);
    /* 
    maaif.u-lits.com
    */
});
