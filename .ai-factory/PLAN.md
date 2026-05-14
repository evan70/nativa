# Plan: Create Admin Section for Cardboard Module

## Goal
Create an admin section for the cardboard module that follows the Marko admin section pattern and uses BEM methodology for any frontend components (if applicable).

## Steps

### 1. Create Admin Section Class
- Create `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/CardboardAdminSection.php`
- Implement `AdminSectionInterface`
- Use `#[AdminSection]` attribute with appropriate id, label, icon, and sortOrder
- Implement `getMenuItems()` to return menu items for cardboard admin functionality

### 2. Define Menu Items
- Menu items should include:
  - Cardboard overview/dashboard
  - Card management (if applicable)
  - Settings/configuration

### 3. Follow BEM for Frontend (if applicable)
- If the admin section requires custom frontend components (TS/CSS), follow BEM methodology:
  - Block: `cardboard-admin` (or similar)
  - Elements: `cardboard-admin__[element]`
  - Modifiers: `cardboard-admin--[modifier]`
- Place TS in `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/[SectionName]AdminSection.ts`
- Place CSS in `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/[section-name]-admin.css`
- Ensure CSS follows BEM naming conventions

### 4. Update Module Configuration
- Ensure the module's `module.php` properly registers any admin-related files if needed
- Check if admin discovery is automatic (should be via attributes)

### 5. Add Dashboard Widgets (Optional)
- Consider creating dashboard widgets for cardboard statistics
- Implement `DashboardWidgetInterface` if needed

### 6. Permissions
- Consider defining permissions for cardboard admin actions using `#[AdminPermission]` attributes

## Files to Create
1. `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/CardboardAdminSection.php`
2. (Optional) `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/CardboardAdminSection.ts`
3. (Optional) `/home/evan/dev/05/nativa/modules/cardboard/src/Admin/cardboard-admin.css`

## Reference
- Existing admin section: `/home/evan/dev/05/nativa/modules/blog/src/Admin/BlogAdminSection.php`
- Admin section interface: `/home/evan/dev/05/nativa/packages/admin/src/Contracts/AdminSectionInterface.php`
- BEM guidelines: `/home/evan/dev/05/nativa/templates/RULES.md`

## Testing
- Verify admin section appears in admin menu
- Check that menu items link to correct routes
- Validate BEM naming if frontend components are created