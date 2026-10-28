# Inventaris controller

Generated statis oleh tools/knowledge.py refresh. Deklarasi source, bukan bukti runtime/schema hidup.


## AdminView

Source: `app/Http/Controllers/AdminView.php`; SHA256 `e9ee72dc8878`.

- L15 `public function ViewUser()`
- L32 `public function ViewBlog()`
- L50 `public function ActiveUser($id)`
- L73 `public function Getkategori()`
- L92 `public function Postkategori(Request $request)`
- L125 `public function Updatekategori(Request $request, $id)`
- L159 `public function destroyKategori($id)`

## AuthController

Source: `app/Http/Controllers/AuthController.php`; SHA256 `863c1ba10672`.

- L23 `public function login(Request $request)`
- L54 `public function sendForgotPassword(Request $request)`
- L117 `public function Forgotpassword(Request $request, $token)`
- L162 `public function logout(Request $request)`

## Blog

Source: `app/Http/Controllers/Blog.php`; SHA256 `d248cfe6781b`.

- L14 `public function Getblog()`
- L33 `public function Postblog(Request $request)`
- L71 `public function get_detail_blog($id)`
- L90 `public function update(Request $request, $id)`
- L129 `public function destroy($id)`

## Controller

Source: `app/Http/Controllers/Controller.php`; SHA256 `663e9f40be59`.


## CustomerController

Source: `app/Http/Controllers/CustomerController.php`; SHA256 `a648c37dd2d8`.

- L15 `public function __construct()`
- L20 `public function index()`
- L27 `public function show($uuid)`
- L37 `public function store(Request $request)`
- L55 `public function update($uuid, Request $request)`
- L82 `public function destroy($uuid)`
- L98 `public function search($name)`

## FinanceController

Source: `app/Http/Controllers/FinanceController.php`; SHA256 `087a6258e613`.

- L26 `public function __construct()`
- L36 `public function index(Request $request)`
- L69 `public function show($uuid)`
- L79 `public function update($uuid, Request $request)`
- L104 `public function destroy($uuid)`
- L120 `public function indexInstallment($uuidPayment)`
- L154 `public function storeInstallment($uuidPayment, Request $request)`
- L203 `public function showInstallment($uuid)`
- L214 `public function updateInstallment($uuid, Request $request)`
- L304 `public function destroyInstallment($uuid)`
- L363 `public function indexBank()`
- L371 `public function storeBank(Request $request)`
- L388 `public function showBank($uuid)`
- L396 `public function updateBank($uuid, Request $request)`
- L422 `public function destroyBank($uuid)`
- L439 `public function indexFinancialRecord(Request $request)`
- L500 `public function showRecordIncome($uuid)`
- L511 `public function showRecordOutcome($uuid)`
- L521 `public function indexFinancialRecordSA(Request $request)`
- L561 `public function showRecordSA($uuid)`
- L619 `public function indexRecord($uuidOrder)`
- L652 `public function storeRecord($uuidOrder, Request $request)`
- L689 `public function storeRefund($uuidOrder, Request $request)`
- L730 `public function showRecord($uuid)`
- L741 `public function updateRecord($uuid, Request $request)`
- L781 `public function destroyRecord($uuid)`
- L797 `public function invoicePrint($uuidPayment)`
- L825 `public function installmentDetailPrint($uuid)`
- L845 `public function indexFinanceSA()`

## LogisticController

Source: `app/Http/Controllers/LogisticController.php`; SHA256 `2986777f0483`.

- L23 `public function __construct()`
- L32 `public function indexInventory(Request $request)`
- L61 `public function storeInventory(Request $request)`
- L81 `public function showInventory($uuid)`
- L91 `public function updateInventory(Request $request, $uuid)`
- L118 `public function destroyInventory($uuid)`
- L134 `public function indexTransfer(Request $request)`
- L146 `public function storeTransfer(Request $request)`
- L208 `public function showTransfer($uuid)`
- L215 `public function updateTransfer(Request $request, $uuid)`
- L304 `public function destroyTransfer($uuid)`
- L321 `public function requestTransfer($uuidOrder, Request $request)`

## MenuController

Source: `app/Http/Controllers/MenuController.php`; SHA256 `091a6b3e4ace`.

- L22 `public function __construct()`
- L30 `public function index(Request $request)`
- L64 `public function show($uuid)`
- L74 `public function showOrder($uuidOrder)`
- L126 `public function store(Request $request)`
- L163 `public function update($uuid, Request $request)`
- L203 `public function destroy($uuid)`
- L218 `public function indexCategory()`
- L226 `public function storeCategory(Request $request)`
- L240 `public function showCategory($uuid)`
- L250 `public function updateCategory($uuid, Request $request)`
- L275 `public function destroyCategory($uuid)`
- L290 `public function indexRecipes($uuidMenu)`
- L298 `public function storeRecipes($uuidMenu, Request $request)`
- L328 `public function updateRecipes($uuidMenu, Request $request)`
- L378 `public function destroyRecipes($uuid)`

## OrderController

Source: `app/Http/Controllers/OrderController.php`; SHA256 `869aa926664b`.

- L52 `public function __construct()`
- L74 `public function index(Request $request)`
- L100 `public function show($uuid)`
- L110 `public function update($uuid, Request $request)`
- L206 `public function store(Request $request)`
- L272 `public function storeWithCustomer(Request $request)`
- L393 `public function destroy($uuid)`
- L428 `public function indexMenus($uuidOrder, Request $request)`
- L541 `public function storeMenus($uuidOrder, Request $request)`
- L610 `public function showMenus($uuidOrderPackage)`
- L617 `public function updateMenus($uuidOrder, Request $request)`
- L836 `public function listMenu($uuidOrder, Request $request)`
- L947 `public function addMenus($uuidOrder, Request $request)`
- L1004 `public function refreshMenu($uuidOrder, Request $request)`
- L1145 `public function restorePackage($uuidOrderPackage)`
- L1160 `public function restorePackageMenu($uuidOrderPackageMenu)`
- L1175 `public function restoreCustomMenu($uuidOrderCustomMenu)`
- L1190 `public function getAllFieldCoordinator()`
- L1202 `public function updateFieldCoordinator($uuid, Request $request)`
- L1229 `public function indexLogistic($uuidOrder)`
- L1236 `public function storeLogistic($uuidOrder, Request $request)`
- L1268 `public function updateLogistic($uuidOrder, Request $request)`
- L1314 `public function destroyLogistic($uuid)`
- L1329 `public function indexEventStaff($uuidOrder)`
- L1337 `public function storeEventStaff($uuidOrder, Request $request)`
- L1360 `public function showEventStaff($uuid)`
- L1368 `public function updateEventStaff($uuidOrder, Request $request)`
- L1413 `public function destroyEventStaff($uuid)`
- L1428 `public function eventEnd($uuidOrder, Request $request)`
- L1503 `public function eventEndUpdate($uuidOrder, Request $request)`
- L1507 `public function readPurchasePlan($uuidOrder)`
- L1584 `public function orderPrint($uuid, $type = null)`
- L1600 `public function printByPeriod(Request $request)`
- L1650 `public function purchasePlanPrint($uuidOrder)`
- L1722 `public function logisticPrint($uuidOrder)`

## PackageController

Source: `app/Http/Controllers/PackageController.php`; SHA256 `314d3f8703b1`.

- L22 `public function __construct()`
- L31 `public function landingPage(Request $request)`
- L95 `public function index(Request $request)`
- L109 `public function show($uuid)`
- L118 `public function update($uuid, Request $request)`
- L145 `public function destroy($uuid)`
- L161 `public function indexPackageData($uuidPackage)`
- L168 `public function indexPackageDataV2($uuidPackage)`
- L189 `public function storePackageData($uuidPackage, Request $request)`
- L216 `public function store(Request $request)`
- L235 `public function showPackageData($uuid)`
- L244 `public function updatePackageData($uuidPackage, Request $request)`
- L276 `public function destroyPackageData($uuid)`
- L292 `public function indexPackageStall($uuidPackage)`
- L299 `public function storePackageStall($uuidPackage, Request $request)`
- L326 `public function showPackageStall($uuid)`
- L335 `public function updatePackageStall($uuidPackage, Request $request)`
- L367 `public function destroyPackageStall($uuid)`

## UserController

Source: `app/Http/Controllers/UserController.php`; SHA256 `3fb34dd6bb84`.

- L33 `public function __construct()`
- L47 `public function indexUser()`
- L57 `public function storeUser(Request $request)`
- L90 `public function showUser($uuid)`
- L102 `public function updateUser($uuid, Request $request)`
- L139 `public function updatePasswordByAdmin($uuid, Request $request)`
- L164 `public function updateUserRole($uuid, Request $request)`
- L194 `public function reactiveUser($uuid)`
- L213 `public function suspendUser($uuid)`
- L235 `public function indexProfile()`
- L249 `public function updateProfile(Request $request)`
- L279 `public function updatePassword(Request $request)`
- L310 `public function indexActivities(Request $request)`
- L324 `public function showActivity($uuid)`
- L338 `public function indexRole()`
- L351 `public function showRole($uuid)`
- L365 `public function indexStaff()`
- L373 `public function storeStaff(Request $request)`
- L389 `public function showStaff($uuid)`
- L399 `public function updateStaff($uuid, Request $request)`
- L425 `public function destroyStaff($uuid)`
- L441 `public function indexMeet(Request $request)`
- L455 `public function showMeet($uuid)`
- L465 `public function storeMeet(Request $request)`
- L487 `public function updateMeet($uuid, Request $request)`
- L516 `public function destroyMeet($uuid)`
- L532 `public function dashboardSuperAdmin(Request $request)`
- L575 `public function dashboardAdmin(Request $request)`
- L624 `public function dashboardFinance(Request $request)`
- L689 `public function indexTestimonial()`
- L696 `public function storeTestimonial(Request $request)`
- L720 `public function showTestimonial($uuid)`
- L730 `public function updateTestimonial($uuid, Request $request)`
- L764 `public function updateTestimonialCustomer($uuid, Request $request)`
- L798 `public function destroyTestimonial($uuid)`
