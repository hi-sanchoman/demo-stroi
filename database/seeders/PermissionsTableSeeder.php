<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'id'    => 1,
                'title' => 'user_management_access',
            ],
            [
                'id'    => 2,
                'title' => 'permission_create',
            ],
            [
                'id'    => 3,
                'title' => 'permission_edit',
            ],
            [
                'id'    => 4,
                'title' => 'permission_show',
            ],
            [
                'id'    => 5,
                'title' => 'permission_delete',
            ],
            [
                'id'    => 6,
                'title' => 'permission_access',
            ],
            [
                'id'    => 7,
                'title' => 'role_create',
            ],
            [
                'id'    => 8,
                'title' => 'role_edit',
            ],
            [
                'id'    => 9,
                'title' => 'role_show',
            ],
            [
                'id'    => 10,
                'title' => 'role_delete',
            ],
            [
                'id'    => 11,
                'title' => 'role_access',
            ],
            [
                'id'    => 12,
                'title' => 'user_create',
            ],
            [
                'id'    => 13,
                'title' => 'user_edit',
            ],
            [
                'id'    => 14,
                'title' => 'user_show',
            ],
            [
                'id'    => 15,
                'title' => 'user_delete',
            ],
            [
                'id'    => 16,
                'title' => 'user_access',
            ],
            [
                'id'    => 17,
                'title' => 'basic_c_r_m_access',
            ],
            [
                'id'    => 18,
                'title' => 'crm_status_create',
            ],
            [
                'id'    => 19,
                'title' => 'crm_status_edit',
            ],
            [
                'id'    => 20,
                'title' => 'crm_status_show',
            ],
            [
                'id'    => 21,
                'title' => 'crm_status_delete',
            ],
            [
                'id'    => 22,
                'title' => 'crm_status_access',
            ],
            [
                'id'    => 23,
                'title' => 'crm_customer_create',
            ],
            [
                'id'    => 24,
                'title' => 'crm_customer_edit',
            ],
            [
                'id'    => 25,
                'title' => 'crm_customer_show',
            ],
            [
                'id'    => 26,
                'title' => 'crm_customer_delete',
            ],
            [
                'id'    => 27,
                'title' => 'crm_customer_access',
            ],
            [
                'id'    => 28,
                'title' => 'crm_note_create',
            ],
            [
                'id'    => 29,
                'title' => 'crm_note_edit',
            ],
            [
                'id'    => 30,
                'title' => 'crm_note_show',
            ],
            [
                'id'    => 31,
                'title' => 'crm_note_delete',
            ],
            [
                'id'    => 32,
                'title' => 'crm_note_access',
            ],
            [
                'id'    => 33,
                'title' => 'crm_document_create',
            ],
            [
                'id'    => 34,
                'title' => 'crm_document_edit',
            ],
            [
                'id'    => 35,
                'title' => 'crm_document_show',
            ],
            [
                'id'    => 36,
                'title' => 'crm_document_delete',
            ],
            [
                'id'    => 37,
                'title' => 'crm_document_access',
            ],
            [
                'id'    => 38,
                'title' => 'product_management_access',
            ],
            [
                'id'    => 39,
                'title' => 'product_category_create',
            ],
            [
                'id'    => 40,
                'title' => 'product_category_edit',
            ],
            [
                'id'    => 41,
                'title' => 'product_category_show',
            ],
            [
                'id'    => 42,
                'title' => 'product_category_delete',
            ],
            [
                'id'    => 43,
                'title' => 'product_category_access',
            ],
            [
                'id'    => 44,
                'title' => 'product_tag_create',
            ],
            [
                'id'    => 45,
                'title' => 'product_tag_edit',
            ],
            [
                'id'    => 46,
                'title' => 'product_tag_show',
            ],
            [
                'id'    => 47,
                'title' => 'product_tag_delete',
            ],
            [
                'id'    => 48,
                'title' => 'product_tag_access',
            ],
            [
                'id'    => 49,
                'title' => 'product_create',
            ],
            [
                'id'    => 50,
                'title' => 'product_edit',
            ],
            [
                'id'    => 51,
                'title' => 'product_show',
            ],
            [
                'id'    => 52,
                'title' => 'product_delete',
            ],
            [
                'id'    => 53,
                'title' => 'product_access',
            ],
            [
                'id'    => 54,
                'title' => 'user_alert_create',
            ],
            [
                'id'    => 55,
                'title' => 'user_alert_show',
            ],
            [
                'id'    => 56,
                'title' => 'user_alert_delete',
            ],
            [
                'id'    => 57,
                'title' => 'user_alert_access',
            ],
            [
                'id'    => 58,
                'title' => 'task_management_access',
            ],
            [
                'id'    => 59,
                'title' => 'task_status_create',
            ],
            [
                'id'    => 60,
                'title' => 'task_status_edit',
            ],
            [
                'id'    => 61,
                'title' => 'task_status_show',
            ],
            [
                'id'    => 62,
                'title' => 'task_status_delete',
            ],
            [
                'id'    => 63,
                'title' => 'task_status_access',
            ],
            [
                'id'    => 64,
                'title' => 'task_tag_create',
            ],
            [
                'id'    => 65,
                'title' => 'task_tag_edit',
            ],
            [
                'id'    => 66,
                'title' => 'task_tag_show',
            ],
            [
                'id'    => 67,
                'title' => 'task_tag_delete',
            ],
            [
                'id'    => 68,
                'title' => 'task_tag_access',
            ],
            [
                'id'    => 69,
                'title' => 'task_create',
            ],
            [
                'id'    => 70,
                'title' => 'task_edit',
            ],
            [
                'id'    => 71,
                'title' => 'task_show',
            ],
            [
                'id'    => 72,
                'title' => 'task_delete',
            ],
            [
                'id'    => 73,
                'title' => 'task_access',
            ],
            [
                'id'    => 74,
                'title' => 'tasks_calendar_access',
            ],
            [
                'id'    => 75,
                'title' => 'contact_management_access',
            ],
            [
                'id'    => 76,
                'title' => 'contact_company_create',
            ],
            [
                'id'    => 77,
                'title' => 'contact_company_edit',
            ],
            [
                'id'    => 78,
                'title' => 'contact_company_show',
            ],
            [
                'id'    => 79,
                'title' => 'contact_company_delete',
            ],
            [
                'id'    => 80,
                'title' => 'contact_company_access',
            ],
            [
                'id'    => 81,
                'title' => 'contact_contact_create',
            ],
            [
                'id'    => 82,
                'title' => 'contact_contact_edit',
            ],
            [
                'id'    => 83,
                'title' => 'contact_contact_show',
            ],
            [
                'id'    => 84,
                'title' => 'contact_contact_delete',
            ],
            [
                'id'    => 85,
                'title' => 'contact_contact_access',
            ],
            [
                'id'    => 86,
                'title' => 'profile_password_edit',
            ],
        ];

        Permission::insert($permissions);
    }
}
