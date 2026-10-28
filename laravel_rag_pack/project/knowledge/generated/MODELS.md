# Inventaris model

Generated statis oleh tools/knowledge.py refresh. Deklarasi source, bukan bukti runtime/schema hidup.


## ActivityModel

Source: `app/Models/ActivityModel.php`; SHA256 `585761d44ed0`.

Tabel: `activities`.
Fillable: `uuid`, `user_id`, `description`.
Traits: `HasFactory, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L24 `public function user()`
- L29 `public function getAll(array $filter)`
- L48 `public function getById(string $id)`
- Relasi `user`: `belongsTo` → UserModel; keys=user_id, uuid

## BankModel

Source: `app/Models/BankModel.php`; SHA256 `c571056f15a6`.

Tabel: `banks`.
Fillable: `uuid`, `name`, `account_number`, `account_owner`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L33 `public function paymentInstallment()`
- L38 `public function getAll()`
- L53 `public function store(array $payload)`
- L68 `public function getById(string $id)`
- L83 `public function edit(array $payload, string $id)`
- L98 `public function drop(string $id)`
- Relasi `paymentInstallment`: `hasMany` → PaymentInstallmentsModel; keys=payment_bank_id, uuid

## CustomerModel

Source: `app/Models/CustomerModel.php`; SHA256 `5515653c1a84`.

Tabel: `customers`.
Fillable: `uuid`, `name`, `phone_number_1`, `phone_number_2`, `address`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `protected static function booted()`
- L48 `public function order()`
- L53 `public function getAll()`
- L68 `public function store(array $payload)`
- L83 `public function getById(string $id)`
- L103 `public function getByName(string $name)`
- L121 `public function edit(array $payload, string $id)`
- L136 `public function drop(string $id)`
- Relasi `order`: `hasMany` → OrdersCustomersModel; keys=customer_id, uuid

## EventLogisticModel

Source: `app/Models/EventLogisticModel.php`; SHA256 `15280fed9e6c`.

Tabel: `event_logistics`.
Fillable: `uuid`, `order_id`, `item_id`, `quantity`, `items_not_returned`, `in_client_hands`, `notes`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L37 `public function order()`
- L42 `public function inventory()`
- L47 `public function getAll($orderUuid)`
- L62 `public function store(array $payload)`
- L77 `public function getById(string $id)`
- L92 `public function getByOrderAndItemId(string $order_id, string $item_id)`
- L107 `public function getByDateAndItemId($filter, $item_id)`
- L125 `public function edit(array $payload, string $id)`
- L140 `public function drop(string $id)`
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid
- Relasi `inventory`: `belongsTo` → InventoryItemModel; keys=item_id, uuid

## EventStaffModel

Source: `app/Models/EventStaffModel.php`; SHA256 `eb94bfecdb36`.

Tabel: `event_staff`.
Fillable: `uuid`, `order_id`, `staff_id`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L32 `public function order()`
- L37 `public function staff()`
- L42 `public function getAll($uuidOrder)`
- L57 `public function store(array $payload)`
- L72 `public function getById(string $id)`
- L87 `public function edit(array $payload, string $id)`
- L102 `public function drop(string $id)`
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid
- Relasi `staff`: `belongsTo` → StaffModel; keys=staff_id, uuid

## EventTransferItemModel

Source: `app/Models/EventTransferItemModel.php`; SHA256 `c87fb3bb6b02`.

Tabel: `event_transfer_items`.
Fillable: `uuid`, `event_transfer_id`, `item_id`, `quantity`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L32 `public function eventTransfer()`
- L37 `public function inventory()`
- L42 `public function getAll()`
- L57 `public function store(array $payload)`
- L72 `public function getById(string $id)`
- L87 `public function getAllByTransferId(string $id)`
- L102 `public function getByOrderAndItem($uuidOrder, $item_id)`
- L120 `public function edit(array $payload, string $id)`
- L135 `public function drop(string $id)`
- L150 `public function dropByTransfer(string $id)`
- Relasi `eventTransfer`: `belongsTo` → EventTransferModel; keys=event_transfer_id, uuid
- Relasi `inventory`: `belongsTo` → InventoryItemModel; keys=item_id, uuid

## EventTransferModel

Source: `app/Models/EventTransferModel.php`; SHA256 `f3e490ab6ff6`.

Tabel: `event_transfers`.
Fillable: `uuid`, `status`, `order_id_from`, `order_id_to`, `note`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L35 `protected static function booted()`
- L50 `public function orderFrom()`
- L55 `public function orderTo()`
- L60 `public function transferItems()`
- L65 `public function getAll(array $filter)`
- L94 `public function store(array $payload)`
- L118 `public function getById(string $id)`
- L133 `public function edit(array $payload, string $id)`
- L148 `public function drop(string $id)`
- Relasi `orderFrom`: `belongsTo` → OrdersCustomersModel; keys=order_id_from, uuid
- Relasi `orderTo`: `belongsTo` → OrdersCustomersModel; keys=order_id_to, uuid
- Relasi `transferItems`: `hasMany` → EventTransferItemModel; keys=event_transfer_id, uuid

## FinancialRecordModel

Source: `app/Models/FinancialRecordModel.php`; SHA256 `6a16f02d650f`.

Tabel: `financial_records`.
Fillable: `uuid`, `order_id`, `date`, `amount`, `description`, `record_image_id`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L35 `protected static function booted()`
- L59 `public function image()`
- L64 `public function order()`
- L69 `public function getAll(array $filter)`
- L112 `public function totalRecord(array $filter)`
- L151 `public function totalByOrder(string $orderId)`
- L166 `public function getAllByOrder($id)`
- L181 `public function getExpense(array $filter)`
- L202 `public function store(array $payload)`
- L217 `public function getById(string $id)`
- L232 `public function getByOrderNDate($order_id, $date)`
- L248 `public function edit(string $id, array $payload)`
- L263 `public function drop(string $id)`
- Relasi `image`: `belongsTo` → Images; keys=record_image_id, uuid
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid

## Images

Source: `app/Models/Images.php`; SHA256 `852437790dc5`.

Tabel: `images`.
Fillable: `uuid`, `type`, `name`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function menu()`
- L39 `public function installment()`
- L44 `public function store($imageFile, $type)`
- L69 `public function edit($imageFile, $type, $uuid)`
- Relasi `menu`: `hasMany` → MenuModel; keys=image_id, uuid
- Relasi `installment`: `hasMany` → PaymentInstallmentsModel; keys=payment_id, uuid

## InventoryItemModel

Source: `app/Models/InventoryItemModel.php`; SHA256 `b3c89bc2a375`.

Tabel: `inventory_items`.
Fillable: `uuid`, `name`, `quantity`, `status`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L33 `public function orderLogistic()`
- L37 `public function transferLogistic()`
- L42 `public function getAll()`
- L57 `public function store(array $payload)`
- L72 `public function getById(string $id)`
- L87 `public function edit(array $payload, string $id)`
- L102 `public function drop(string $id)`
- Relasi `orderLogistic`: `hasMany` → EventLogisticModel; keys=item_id, uuid
- Relasi `transferLogistic`: `hasMany` → EventTransferItemModel; keys=item_id, uuid

## MBlog

Source: `app/Models/MBlog.php`; SHA256 `44778e73d337`.

Tabel: `blog`.
Fillable: `title`, `content`, `kategori`.
Traits: `HasFactory`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.

## MKategori

Source: `app/Models/MKategori.php`; SHA256 `e939c5388699`.

Tabel: `kategori`.
Fillable: `kategori`.
Traits: `HasFactory`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.

## MPasswordreset

Source: `app/Models/MPasswordreset.php`; SHA256 `04180062b9f5`.

Tabel: `password_reset_tokens`.
Fillable: `email`, `token`.
Traits: `HasFactory`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.

## MeetingScheduleModel

Source: `app/Models/MeetingScheduleModel.php`; SHA256 `ef40ab6b140e`.

Tabel: `meeting_schedules`.
Fillable: `uuid`, `title`, `date`, `start_time`, `end_time`, `location`, `note`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L35 `public function getAll(array $filter)`
- L89 `public function store(array $payload)`
- L104 `public function getById(string $id)`
- L126 `public function edit(array $payload, string $id)`
- L141 `public function drop(string $id)`

## MenuCategoryModel

Source: `app/Models/MenuCategoryModel.php`; SHA256 `c466c9822c13`.

Tabel: `menu_categories`.
Fillable: `uuid`, `name`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L31 `public function menu()`
- L36 `public function packageData()`
- L41 `public function getAll()`
- L56 `public function store(array $payload)`
- L71 `public function getById(string $id)`
- L87 `public function edit(array $payload, string $id)`
- L102 `public function drop(string $id)`
- Relasi `menu`: `hasMany` → MenuModel; keys=category_id, uuid
- Relasi `packageData`: `hasMany` → PackagesDataModel; keys=category_id, uuid

## MenuModel

Source: `app/Models/MenuModel.php`; SHA256 `aea2b8cefe8e`.

Tabel: `menus`.
Fillable: `uuid`, `name`, `price`, `category_id`, `type`, `description`, `image_id`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L36 `public function category()`
- L41 `public function foodStall()`
- L46 `public function image()`
- L51 `public function getAll($limit = null, $filter = [])`
- L76 `public function store(array $payload)`
- L94 `public function getById(string $id)`
- L109 `public function getByOrderId(string $orderId)`
- L126 `public function getFoodStallByOrderId(string $orderId)`
- L144 `public function edit(array $payload, string $id)`
- L159 `public function drop(string $id)`
- Relasi `category`: `belongsTo` → MenuCategoryModel; keys=category_id, uuid
- Relasi `foodStall`: `hasMany` → PackageFoodStallModel; keys=menu_id, uuid
- Relasi `image`: `belongsTo` → Images; keys=image_id, uuid

## OrderCustomizationModel

Source: `app/Models/OrderCustomizationModel.php`; SHA256 `d82bb69ec3e2`.

Tabel: `order_customizations`.
Fillable: `uuid`, `order_id`, `menu_id`, `portion`, `details`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function order()`
- L38 `public function menu()`
- L43 `public function getAll($order_id)`
- L58 `public function getAllOrder($order_id, array $filter = null)`
- L81 `public function store(array $payload)`
- L96 `public function getById(string $id)`
- L111 `public function getByIdDeleted(string $id)`
- L126 `public function getByOrder($uuidOrder)`
- L141 `public function edit(array $payload, string $id)`
- L156 `public function drop(string $id)`
- L171 `public function manyDrop(array $array)`
- L186 `public function restoreById(string $id)`
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid
- Relasi `menu`: `belongsTo` → MenuModel; keys=menu_id, uuid

## OrderDetailModel

Source: `app/Models/OrderDetailModel.php`; SHA256 `35276c673861`.

Tabel: `order_details`.
Fillable: `uuid`, `order_id`, `akad_start`, `akad_end`, `resepsi_start`, `resepsi_end`, `nuance`, `general_buffet`, `vip_buffet`, `vip_table`, `wedding_food_table`, `akad_table`, `reception_table`, `for_naib`, `ayam_bekakak_nasi_punar`, `mica_for_besan`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L45 `public function order()`
- L50 `public function getAll($order_id)`
- L65 `public function store(array $payload)`
- L80 `public function getById(string $id)`
- L95 `public function getByOrder($uuidOrder)`
- L110 `public function edit(array $payload)`
- L125 `public function drop(string $id)`
- L140 `public function dropByOrderId(String $id)`
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid

## OrderPackageItems

Source: `app/Models/OrderPackageItems.php`; SHA256 `8cbe2c711dec`.

Tabel: `order_package_items`.
Fillable: `uuid`, `menu_id`, `portion`, `details`, `status`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L35 `public function orderPackageRel()`
- L40 `public function menu()`
- L45 `public function category()`
- L55 `public function getAll()`
- L70 `public function store(array $payload)`
- L87 `public function getById(string $id)`
- L102 `public function getByIdDeleted(string $id)`
- L117 `public function getByOrder($uuidOrder)`
- L136 `public function getAllOrder($uuidOrder, $status = null, $arrUuidPackage = null)`
- L181 `public function edit(array $payload, string $id)`
- L196 `public function addEditMenu(array $payload)`
- L216 `public function manyUpdate(array $payload)`
- L231 `public function drop(string $id)`
- L246 `public function manyDrop(array $array)`
- L261 `public function updateStatusBatchToFalse(array $array)`
- L276 `public function restoreById(string $id)`
- L291 `public function restore(array $payload, string $id)`
- Relasi `orderPackageRel`: `hasMany` → OrderPackageRelModel; keys=order_package_item_id, uuid
- Relasi `menu`: `belongsTo` → MenuModel; keys=menu_id, uuid
- Relasi `category`: `belongsTo` → MenuCategoryModel; keys=category_id, id

## OrderPackageModel

Source: `app/Models/OrderPackageModel.php`; SHA256 `53d9a99c978c`.

Tabel: `order_packages`.
Fillable: `uuid`, `order_id`, `package_id`, `portion`, `details`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `protected static function booted()`
- L49 `public function orderPackageRel()`
- L54 `public function order()`
- L59 `public function package()`
- L64 `public function getAll($order_id)`
- L79 `public function getAllOrder($order_id, array $filter = null)`
- L104 `public function store(array $payload)`
- L119 `public function getById(string $id)`
- L134 `public function getByIdDeleted(string $id)`
- L164 `public function edit(array $payload)`
- L179 `public function drop(string $id)`
- L194 `public function manyDrop(array $array)`
- Relasi `orderPackageRel`: `hasMany` → OrderPackageRelModel; keys=order_package_id, uuid
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid
- Relasi `package`: `belongsTo` → PackagesModel; keys=package_id, uuid

## OrderPackageRelModel

Source: `app/Models/OrderPackageRelModel.php`; SHA256 `9a9276249f50`.

Tabel: `order_package_rel`.
Fillable: `uuid`, `order_package_id`, `order_package_item_id`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L32 `protected static function booted()`
- L60 `public function orderPackageItems()`
- L65 `public function orderPackage()`
- L70 `public function getAllByOrder($uuidOrder)`
- L89 `public function store(array $item)`
- L104 `public function manyDrop(array $array)`
- Relasi `orderPackageItems`: `belongsTo` → OrderPackageItems; keys=order_package_item_id, uuid
- Relasi `orderPackage`: `belongsTo` → OrderPackageModel; keys=order_package_id, uuid

## OrdersCustomersModel

Source: `app/Models/OrdersCustomersModel.php`; SHA256 `53fcc9a8a87d`.

Tabel: `orders_customers`.
Fillable: `uuid`, `order_name`, `customer_id`, `location`, `event_date`, `event_time`, `portion`, `note`, `status`, `field_coordinator_id`, `handover_of_leftovers`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L42 `protected static function booted()`
- L96 `public function financialRecord()`
- L101 `public function logistic()`
- L106 `public function orderPackage()`
- L111 `public function orderCustom()`
- L116 `public function orderFrom()`
- L121 `public function detail()`
- L126 `public function payment()`
- L131 `public function customer()`
- L136 `public function fieldCoordinator()`
- L141 `public function orderTo()`
- L146 `public function getAll(array $filter)`
- L232 `public function store(array $payload)`
- L252 `public function getById(string $id)`
- L281 `public function edit(array $payload, string $id)`
- L296 `public function drop(string $id)`
- L311 `public function dropForce(string $id)`
- L326 `public function getFinance(array $filter)`
- Relasi `financialRecord`: `hasMany` → FinancialRecordModel; keys=order_id, uuid
- Relasi `logistic`: `hasMany` → EventLogisticModel; keys=order_id, uuid
- Relasi `orderPackage`: `hasMany` → OrderPackageModel; keys=order_id, uuid
- Relasi `orderCustom`: `hasMany` → OrderCustomizationModel; keys=order_id, uuid
- Relasi `orderFrom`: `hasMany` → EventTransferModel; keys=order_id_from, uuid
- Relasi `detail`: `hasOne` → OrderDetailModel; keys=order_id, uuid
- Relasi `payment`: `hasOne` → PaymentsModel; keys=order_id, uuid
- Relasi `customer`: `belongsTo` → CustomerModel; keys=customer_id, uuid
- Relasi `fieldCoordinator`: `belongsTo` → UserModel; keys=field_coordinator_id, uuid
- Relasi `orderTo`: `hasMany` → EventTransferModel; keys=order_id_to, uuid

## PackageFoodStallModel

Source: `app/Models/PackageFoodStallModel.php`; SHA256 `8beb44b37b7e`.

Tabel: `package_food_stall`.
Fillable: `uuid`, `packages_id`, `menu_id`, `quantity_percent`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L33 `public function package()`
- L37 `public function menu()`
- L42 `public function getAll($packages_id)`
- L57 `public function store(array $payload)`
- L72 `public function getById(string $id)`
- L87 `public function edit(array $payload)`
- L102 `public function drop(string $id)`
- Relasi `package`: `belongsTo` → PackagesModel; keys=packages_id, uuid
- Relasi `menu`: `belongsTo` → MenuModel; keys=menu_id, uuid

## PackagesDataModel

Source: `app/Models/PackagesDataModel.php`; SHA256 `3d21b26b542e`.

Tabel: `packages_data`.
Fillable: `uuid`, `packages_id`, `category_id`, `quantity_percent`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function package()`
- L39 `public function category()`
- L44 `public function getAll(array $packages_id)`
- L62 `public function getSortCategory(array $packages_id)`
- L83 `public function store(array $payload)`
- L98 `public function getById(string $id)`
- L113 `public function edit(array $payload)`
- L128 `public function drop(string $id)`
- Relasi `package`: `belongsTo` → PackagesModel; keys=packages_id, uuid
- Relasi `category`: `belongsTo` → MenuCategoryModel; keys=category_id, uuid

## PackagesModel

Source: `app/Models/PackagesModel.php`; SHA256 `a8dc5d6a016a`.

Tabel: `packages`.
Fillable: `uuid`, `name`, `description`, `price`, `type`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function packageData()`
- L39 `public function packageFoodStall()`
- L44 `public function getAll($limit = null, array $filter = null)`
- L82 `public function store(array $payload)`
- L97 `public function getById(string $id)`
- L112 `public function getByManyId(array $arrId)`
- L156 `public function edit(array $payload, string $id)`
- L171 `public function drop(string $id)`
- Relasi `packageData`: `hasMany` → PackagesDataModel; keys=packages_id, uuid
- Relasi `packageFoodStall`: `hasMany` → PackageFoodStallModel; keys=packages_id, uuid

## PaymentDetailModel

Source: `app/Models/PaymentDetailModel.php`; SHA256 `c731d8b88a4f`.

Tabel: `payment_details`.
Fillable: `uuid`, `payment_id`, `type`, `price`, `details`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function payment()`
- L39 `public function getAll($uuidPayment)`
- L54 `public function store(array $payload)`
- L69 `public function getById(string $id)`
- L84 `public function edit(array $payload, string $id)`
- L99 `public function drop(string $id)`
- L114 `public function manyDrop(array $id)`
- Relasi `payment`: `belongsTo` → PaymentsModel; keys=payment_id, uuid

## PaymentInstallmentsModel

Source: `app/Models/PaymentInstallmentsModel.php`; SHA256 `f86061075898`.

Tabel: `payment_installments`.
Fillable: `uuid`, `payment_id`, `payment_bank_id`, `type`, `amount`, `payment_datelines`, `payment_image_id`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L36 `protected static function booted()`
- L60 `public function image()`
- L65 `public function payment()`
- L70 `public function bank()`
- L76 `public function getAll(array $filter)`
- L121 `public function totalInstallment(array $filter)`
- L162 `public function totalByOrder(string $orderId)`
- L179 `public function getAllByPayment($id)`
- L194 `public function store(array $payload)`
- L209 `public function getById(string $id)`
- L224 `public function getPrintDataById(string $id)`
- L241 `public function getByInstallment(string $id)`
- L256 `public function getByOrder(string $orderId)`
- L283 `public function sumByPayment(string $paymentId)`
- L300 `public function edit(string $id, array $payload)`
- L315 `public function drop(string $id)`
- Relasi `image`: `belongsTo` → Images; keys=payment_image_id, uuid
- Relasi `payment`: `belongsTo` → PaymentsModel; keys=payment_id, uuid
- Relasi `bank`: `belongsTo` → BankModel; keys=payment_bank_id, uuid

## PaymentsModel

Source: `app/Models/PaymentsModel.php`; SHA256 `c349dae5c586`.

Tabel: `payments`.
Fillable: `uuid`, `order_id`, `amount`, `status`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L32 `protected static function booted()`
- L50 `public function installment()`
- L55 `public function paymentDetails()`
- L60 `public function order()`
- L65 `public function getAll(array $filter)`
- L99 `public function getDPFromPayment(array $filter)`
- L121 `public function store(array $payload)`
- L136 `public function getById(string $id)`
- L152 `public function getByOrder(string $order_id)`
- L168 `public function edit(string $id, array $payload)`
- L183 `public function drop(string $id)`
- Relasi `installment`: `hasMany` → PaymentInstallmentsModel; keys=payment_id, uuid
- Relasi `paymentDetails`: `hasMany` → PaymentDetailModel; keys=payment_id, uuid
- Relasi `order`: `belongsTo` → OrdersCustomersModel; keys=order_id, uuid

## RecipesModel

Source: `app/Models/RecipesModel.php`; SHA256 `663f1a342499`.

Tabel: `recipes`.
Fillable: `uuid`, `menu_id`, `name`, `quantity`, `unit`, `portion`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L35 `public function menu()`
- L40 `public function getAll($menu_id)`
- L55 `public function store(array $payload)`
- L70 `public function getById(string $id)`
- L85 `public function edit(array $payload)`
- L100 `public function drop(string $id)`
- Relasi `menu`: `belongsTo` → MenuModel; keys=menu_id, uuid

## RolesModel

Source: `app/Models/RolesModel.php`; SHA256 `b24a222f7064`.

Tabel: `roles`.
Fillable: `uuid`, `name`.
Traits: `HasFactory, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L30 `public function users()`
- L35 `public function getAll()`
- L50 `public function store(array $payload)`
- L65 `public function getById(string $id)`
- L80 `public function edit(array $payload, string $id)`
- L95 `public function drop(string $id)`
- Relasi `users`: `hasMany` → UserModel; keys=role, uuid

## StaffModel

Source: `app/Models/StaffModel.php`; SHA256 `08687892df20`.

Tabel: `staff`.
Fillable: `uuid`, `name`, `phone_number`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L32 `public function eventStaff()`
- L37 `public function getAll()`
- L52 `public function store(array $payload)`
- L67 `public function getById(string $id)`
- L109 `public function edit(array $payload, string $id)`
- L124 `public function drop(string $id)`
- Relasi `eventStaff`: `hasMany` → EventStaffModel; keys=staff_id, uuid

## TestimoniModel

Source: `app/Models/TestimoniModel.php`; SHA256 `555fe8b77217`.

Tabel: `testimonials`.
Fillable: `uuid`, `customer_id`, `rating`, `testimonial`, `is_filled`.
Traits: `HasFactory, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L34 `public function customer()`
- L39 `public function getAll($limit = null)`
- L65 `public function store(array $payload)`
- L80 `public function getById(string $id)`
- L95 `public function edit(array $payload)`
- L110 `public function drop(string $id)`
- Relasi `customer`: `belongsTo` → CustomerModel; keys=customer_id, uuid

## UserModel

Source: `app/Models/UserModel.php`; SHA256 `ba3c5214d86c`.

Tabel: `users`.
Fillable: `uuid`, `name`, `username`, `password`, `role_id`, `email`, `phone_number`, `is_active`.
Traits: `HasApiTokens, HasFactory, Notifiable, SoftDeletes, Uuid`.
Primary key: tidak dioverride di class; periksa parent/trait/schema.
- L57 `public function role()`
- L61 `public function activities()`
- L66 `public function order()`
- L71 `public function getAll()`
- L86 `public function store(array $payload)`
- L101 `public function getById(string $id)`
- L116 `public function edit(array $payload, string $id)`
- L132 `public function drop(string $id)`
- Relasi `role`: `belongsTo` → RolesModel; keys=role_id, uuid
- Relasi `activities`: `hasMany` → ActivityModel; keys=user_id
- Relasi `order`: `hasMany` → OrdersCustomersModel; keys=field_coordinator_id, uuid
