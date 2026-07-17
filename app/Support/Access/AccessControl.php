<?php

declare(strict_types=1);

namespace App\Support\Access;

/**
 * Single source of truth for the RBAC catalogue: the role list, the full
 * permission list (grouped by module) and the default role → permission map.
 * Consumed by the seeder and by the UI role-management screens.
 */
final class AccessControl
{
    // --- Roles -------------------------------------------------------------
    public const ROLE_SUPER_ADMIN = 'super-admin';
    public const ROLE_COMPANY = 'company';
    public const ROLE_BRAND = 'brand';
    public const ROLE_DEALER = 'dealer';
    public const ROLE_DISTRIBUTOR = 'distributor';
    public const ROLE_SALESMAN = 'salesman';
    public const ROLE_KARIGAR = 'karigar';
    public const ROLE_CONTRACTOR = 'contractor';
    public const ROLE_RETAILER = 'retailer';
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_SUPPORT = 'support';
    public const ROLE_FINANCE = 'finance';
    public const ROLE_VIEWER = 'viewer';

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_COMPANY,
            self::ROLE_BRAND,
            self::ROLE_DEALER,
            self::ROLE_DISTRIBUTOR,
            self::ROLE_SALESMAN,
            self::ROLE_KARIGAR,
            self::ROLE_CONTRACTOR,
            self::ROLE_RETAILER,
            self::ROLE_EMPLOYEE,
            self::ROLE_SUPPORT,
            self::ROLE_FINANCE,
            self::ROLE_VIEWER,
        ];
    }

    /**
     * Permission catalogue grouped by module. Each entry expands to
     * "{module}.{ability}" permission names.
     *
     * @return array<string, list<string>>
     */
    public static function catalogue(): array
    {
        $crud = ['view', 'create', 'update', 'delete', 'restore', 'export'];

        return [
            'banners' => $crud,
            'brands' => $crud,
            'categories' => $crud,
            'products' => array_merge($crud, ['import']),
            'batches' => $crud,
            'qr-codes' => ['view', 'generate', 'print', 'block', 'export'],
            'wallets' => ['view', 'credit', 'debit', 'export'],
            'redemptions' => ['view', 'create', 'approve', 'reject', 'export'],
            'users' => $crud,
            'roles' => ['view', 'create', 'update', 'delete'],
        ];
    }

    /**
     * Flat list of every permission name.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        $permissions = [];

        foreach (self::catalogue() as $module => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = "{$module}.{$ability}";
            }
        }

        return $permissions;
    }

    /**
     * Default permissions granted to each role. Super-admin is handled via a
     * Gate::before short-circuit and therefore is intentionally omitted here.
     *
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::permissions();

        $readOnly = array_values(array_filter(
            $all,
            static fn (string $p): bool => str_ends_with($p, '.view') || str_ends_with($p, '.export'),
        ));

        // Company admins manage everything in their company except role definitions
        // (role management stays with the super-admin).
        $companyManage = array_values(array_filter(
            $all,
            static fn (string $p): bool => ! str_starts_with($p, 'roles.'),
        ));

        return [
            self::ROLE_COMPANY => $companyManage,
            self::ROLE_BRAND => [
                'brands.view', 'brands.update',
                'products.view', 'products.create', 'products.update', 'products.export',
                'categories.view',
                'batches.view', 'batches.create', 'batches.update',
                'qr-codes.view', 'qr-codes.generate', 'qr-codes.print', 'qr-codes.export',
            ],
            self::ROLE_DISTRIBUTOR => [
                'products.view', 'batches.view', 'qr-codes.view',
            ],
            self::ROLE_DEALER => [
                'products.view', 'batches.view', 'qr-codes.view',
            ],
            self::ROLE_SALESMAN => [
                'products.view', 'qr-codes.view',
            ],
            self::ROLE_KARIGAR => [
                'wallets.view', 'redemptions.view', 'redemptions.create',
            ],
            self::ROLE_CONTRACTOR => [
                'wallets.view', 'redemptions.view', 'redemptions.create',
            ],
            self::ROLE_RETAILER => [
                'products.view', 'wallets.view', 'redemptions.view', 'redemptions.create',
            ],
            self::ROLE_EMPLOYEE => [
                'products.view', 'batches.view', 'qr-codes.view',
            ],
            self::ROLE_SUPPORT => [
                'users.view', 'wallets.view', 'redemptions.view',
            ],
            self::ROLE_FINANCE => array_merge($readOnly, [
                'redemptions.view', 'redemptions.approve', 'redemptions.reject', 'redemptions.export',
                'wallets.view', 'wallets.credit', 'wallets.debit',
            ]),
            self::ROLE_VIEWER => $readOnly,
        ];
    }
}
