# Inventaris migration

Generated statis oleh tools/knowledge.py refresh. Deklarasi source, bukan bukti runtime/schema hidup.


## 2014_08_10_164643_create_roles_table

Source: `database/migrations/2014_08_10_164643_create_roles_table.php`; SHA256 `860ac2cddd9e`.

Create tabel: `roles`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `timestamps()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2014_10_12_000000_create_users_table

Source: `database/migrations/2014_10_12_000000_create_users_table.php`; SHA256 `19220615af89`.

Create tabel: `users`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `string(username)`; unique
- L19 `string(password)`
- L20 `char(role)`
- L21 `string(email)`; unique
- L22 `string(phone_number)`
- L23 `rememberToken()`
- L24 `timestamps()`
- L25 `softDeletes()`
- L27 `foreign(role)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2014_10_12_100000_create_password_reset_tokens_table

Source: `database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php`; SHA256 `d4994ee781fc`.

Create tabel: `password_reset_tokens`.
- L15 `id()`
- L16 `string(email)`
- L17 `string(token)`
- L18 `timestamp(created_at)`; nullable
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2019_08_19_000000_create_failed_jobs_table

Source: `database/migrations/2019_08_19_000000_create_failed_jobs_table.php`; SHA256 `1b2dc826a38d`.

Create tabel: `failed_jobs`.
- L15 `id()`
- L16 `string(uuid)`; unique
- L17 `text(connection)`
- L18 `text(queue)`
- L19 `longText(payload)`
- L20 `longText(exception)`
- L21 `timestamp(failed_at)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2019_12_14_000001_create_personal_access_tokens_table

Source: `database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`; SHA256 `5a9c64d94d29`.

Create tabel: `personal_access_tokens`.
- L15 `id()`
- L16 `morphs(tokenable)`
- L17 `string(name)`
- L18 `string(token)`; unique
- L19 `text(abilities)`; nullable
- L20 `timestamp(last_used_at)`; nullable
- L21 `timestamp(expires_at)`; nullable
- L22 `timestamps()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_180233_create_images_table

Source: `database/migrations/2024_08_08_180233_create_images_table.php`; SHA256 `fa6b13d2463f`.

Create tabel: `images`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `enum(type)`
- L18 `string(name)`
- L19 `timestamps()`
- L20 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_180234_create_activities_table

Source: `database/migrations/2024_08_08_180234_create_activities_table.php`; SHA256 `ac304d3ee2e8`.

Create tabel: `activities`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(user_id)`
- L18 `text(description)`
- L19 `timestamps()`
- L20 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_180408_create_customers_table

Source: `database/migrations/2024_08_08_180408_create_customers_table.php`; SHA256 `6eb5a69f6567`.

Create tabel: `customers`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `string(phone_number_1)`
- L19 `string(phone_number_2)`; nullable
- L20 `text(address)`; nullable
- L21 `timestamps()`
- L22 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181156_create_menu_categories_table

Source: `database/migrations/2024_08_08_181156_create_menu_categories_table.php`; SHA256 `4bf2a9f15214`.

Create tabel: `menu_categories`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `timestamps()`
- L19 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181210_create_menus_table

Source: `database/migrations/2024_08_08_181210_create_menus_table.php`; SHA256 `9dabf16b63a7`.

Create tabel: `menus`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `bigInteger(price)`
- L19 `uuid(category_id)`
- L20 `enum(type)`
- L21 `text(description)`
- L22 `uuid(image_id)`; nullable
- L23 `timestamps()`
- L24 `softDeletes()`
- L25 `foreign(category_id)`
- L29 `foreign(image_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181320_create_recipes_table

Source: `database/migrations/2024_08_08_181320_create_recipes_table.php`; SHA256 `bf22bf548745`.

Create tabel: `recipes`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(menu_id)`
- L18 `string(name)`
- L19 `integer(quantity)`
- L20 `text(unit)`
- L21 `integer(portion)`
- L22 `timestamps()`
- L23 `softDeletes()`
- L24 `foreign(menu_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181615_create_packages_table

Source: `database/migrations/2024_08_08_181615_create_packages_table.php`; SHA256 `ba408e63ac7c`.

Create tabel: `packages`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `text(description)`
- L19 `bigInteger(price)`
- L20 `enum(type)`
- L21 `timestamps()`
- L22 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181712_create_packages_datas_table

Source: `database/migrations/2024_08_08_181712_create_packages_datas_table.php`; SHA256 `a31c10588677`.

Create tabel: `packages_data`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(packages_id)`
- L18 `uuid(category_id)`
- L19 `integer(quantity_percent)`
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(packages_id)`
- L26 `foreign(category_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_181905_create_banks_table

Source: `database/migrations/2024_08_08_181905_create_banks_table.php`; SHA256 `43e97cd23410`.

Create tabel: `banks`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `string(account_number)`
- L19 `string(account_owner)`
- L20 `timestamps()`
- L21 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182441_create_orders_customers_table

Source: `database/migrations/2024_08_08_182441_create_orders_customers_table.php`; SHA256 `a9cf83e6c8a1`.

Create tabel: `orders_customers`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(order_name)`
- L18 `uuid(customer_id)`
- L19 `text(location)`; nullable
- L20 `date(event_date)`; nullable
- L21 `time(event_time)`; nullable
- L22 `integer(portion)`; nullable
- L23 `text(note)`; nullable
- L24 `enum(status)`
- L25 `uuid(field_coordinator_id)`; nullable
- L26 `text(handover_of_leftovers)`; nullable
- L27 `timestamps()`
- L28 `softDeletes()`
- L29 `foreign(customer_id)`
- L33 `foreign(field_coordinator_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182541_create_order_packages_table

Source: `database/migrations/2024_08_08_182541_create_order_packages_table.php`; SHA256 `64c49313bcf7`.

Create tabel: `order_packages`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_id)`
- L18 `uuid(package_id)`
- L19 `text(details)`; nullable
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(order_id)`
- L26 `foreign(package_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182602_create_order_customizations_table

Source: `database/migrations/2024_08_08_182602_create_order_customizations_table.php`; SHA256 `9c04a7b6f36e`.

Create tabel: `order_customizations`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_id)`
- L18 `uuid(menu_id)`
- L19 `integer(portion)`
- L20 `text(details)`; nullable
- L21 `timestamps()`
- L22 `softDeletes()`
- L23 `foreign(order_id)`
- L27 `foreign(menu_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182812_create_payments_table

Source: `database/migrations/2024_08_08_182812_create_payments_table.php`; SHA256 `b01cbf039ff4`.

Create tabel: `payments`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_id)`
- L18 `bigInteger(amount)`; nullable
- L19 `enum(status)`
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(order_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182835_create_payment_installments_table

Source: `database/migrations/2024_08_08_182835_create_payment_installments_table.php`; SHA256 `4cbb809c7812`.

Create tabel: `payment_installments`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(payment_id)`
- L18 `integer(instalment)`
- L19 `uuid(payment_bank_id)`
- L20 `enum(type)`
- L21 `bigInteger(amount)`
- L22 `date(payment_datelines)`
- L23 `uuid(payment_image_id)`
- L24 `timestamps()`
- L25 `softDeletes()`
- L26 `foreign(payment_id)`
- L30 `foreign(payment_bank_id)`
- L34 `foreign(payment_image_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_182906_create_financial_records_table

Source: `database/migrations/2024_08_08_182906_create_financial_records_table.php`; SHA256 `442eeb4dca02`.

Create tabel: `financial_records`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_id)`
- L18 `enum(type)`
- L19 `date(date)`
- L20 `bigInteger(amount)`
- L21 `text(description)`; nullable
- L22 `uuid(record_image_id)`
- L23 `timestamps()`
- L24 `softDeletes()`
- L25 `foreign(order_id)`
- L29 `foreign(record_image_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_08_183003_create_inventory_items_table

Source: `database/migrations/2024_08_08_183003_create_inventory_items_table.php`; SHA256 `68a0b18b3b68`.

Create tabel: `inventory_items`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `integer(quantity)`
- L19 `enum(status)`
- L20 `timestamps()`
- L21 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_15_111731_create_order_package_items_table

Source: `database/migrations/2024_08_15_111731_create_order_package_items_table.php`; SHA256 `425938201ad2`.

Create tabel: `order_package_items`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_package_id)`
- L18 `uuid(menu_id)`
- L19 `integer(portion)`
- L20 `text(details)`; nullable
- L21 `timestamps()`
- L22 `softDeletes()`
- L23 `foreign(order_package_id)`
- L27 `foreign(menu_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_08_17_010235_create_package_food_stall_table

Source: `database/migrations/2024_08_17_010235_create_package_food_stall_table.php`; SHA256 `65e3e364fd69`.

Create tabel: `package_food_stall`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(packages_id)`
- L18 `uuid(menu_id)`
- L19 `integer(quantity_percent)`
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(packages_id)`
- L26 `foreign(menu_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_06_123253_create_staff_table

Source: `database/migrations/2024_09_06_123253_create_staff_table.php`; SHA256 `08b1a5dde38e`.

Create tabel: `staff`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `string(name)`
- L18 `string(phone_number)`
- L19 `softDeletes()`
- L20 `timestamps()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_07_003440_create_event_staff_table

Source: `database/migrations/2024_09_07_003440_create_event_staff_table.php`; SHA256 `23d368b82795`.

Create tabel: `event_staff`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(order_id)`
- L18 `uuid(staff_id)`
- L19 `enum(status)`
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(order_id)`
- L26 `foreign(staff_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_11_055250_create_event_transfers_table

Source: `database/migrations/2024_09_11_055250_create_event_transfers_table.php`; SHA256 `6124df8d4250`.

Create tabel: `event_transfers`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `enum(status)`
- L18 `uuid(order_id_from)`; nullable
- L19 `uuid(order_id_to)`; nullable
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(order_id_from)`
- L26 `foreign(order_id_to)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_11_055350_create_event_transfer_items_table

Source: `database/migrations/2024_09_11_055350_create_event_transfer_items_table.php`; SHA256 `8af787b5ad76`.

Create tabel: `event_transfer_items`.
- L15 `id()`
- L16 `uuid(uuid)`; unique
- L17 `uuid(event_transfer_id)`
- L18 `uuid(item_id)`
- L19 `integer(quantity)`
- L20 `timestamps()`
- L21 `softDeletes()`
- L22 `foreign(event_transfer_id)`
- L26 `foreign(item_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_16_052336_create_testimonials_table

Source: `database/migrations/2024_09_16_052336_create_testimonials_table.php`; SHA256 `aaa9291efa53`.

Create tabel: `testimonials`.
- L15 `id()`
- L16 `uuid(uuid)`
- L17 `uuid(customer_id)`
- L18 `integer(rating)`
- L19 `text(testimonial)`
- L20 `boolean(is_filled)`
- L21 `timestamps()`
- L22 `softDeletes()`
- L23 `foreign(customer_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_20_065000_create_event_logistics_table

Source: `database/migrations/2024_09_20_065000_create_event_logistics_table.php`; SHA256 `70500f0ea2d1`.

Create tabel: `event_logistics`.
- L16 `id()`
- L17 `uuid(uuid)`
- L18 `uuid(order_id)`
- L19 `uuid(item_id)`
- L20 `integer(quantity)`
- L21 `integer(quantity_return)`
- L22 `integer(broken_items)`
- L23 `integer(in_client_hands)`
- L24 `text(notes)`
- L25 `timestamps()`
- L26 `softDeletes()`
- L27 `foreign(order_id)`
- L31 `foreign(item_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_09_22_185653_create_meeting_schedules_table

Source: `database/migrations/2024_09_22_185653_create_meeting_schedules_table.php`; SHA256 `b280ec893ae6`.

Create tabel: `meeting_schedules`.
- L15 `id()`
- L16 `uuid(uuid)`
- L17 `string(title)`
- L18 `date(date)`
- L19 `time(start_time)`
- L20 `time(end_time)`; nullable
- L21 `text(location)`; nullable
- L22 `text(note)`; nullable
- L23 `timestamps()`
- L24 `softDeletes()`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.

## 2024_10_04_135242_create_order_details_table

Source: `database/migrations/2024_10_04_135242_create_order_details_table.php`; SHA256 `5d2d78d78e48`.

Create tabel: `order_details`.
- L15 `id()`
- L16 `uuid(uuid)`
- L17 `uuid(order_id)`
- L18 `time(akad_start)`; nullable
- L19 `time(akad_end)`; nullable
- L20 `time(resepsi_start)`; nullable
- L21 `time(resepsi_end)`; nullable
- L22 `text(nuance)`; nullable
- L23 `integer(general_buffet)`; nullable
- L24 `integer(vip_buffet)`; nullable
- L25 `integer(vip_table)`; nullable
- L26 `integer(wedding_food_table)`; nullable
- L27 `integer(akad_table)`; nullable
- L28 `integer(reception_table)`; nullable
- L29 `text(for_naib)`; nullable
- L30 `enum(ayam_bekakak_nasi_punar)`
- L31 `enum(mica_for_besan)`
- L32 `timestamps()`
- L33 `softDeletes()`
- L34 `foreign(order_id)`
Enum/default/foreign target lengkap: baca migration asli; generator hanya mengekspor nama dan tipe deklarasi.
