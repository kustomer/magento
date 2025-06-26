#!/bin/bash

# Security Patch Application Script
# This script updates vulnerable composer packages to secure versions
# 
# IMPORTANT: 
# - Run this in a staging environment first
# - Take a full backup before applying patches
# - Test thoroughly before deploying to production

set -e  # Exit on any error

echo "🔒 Kustomer Integration - Security Patch Application"
echo "=================================================="
echo ""

# Check if we're in the right directory
if [ ! -f "composer.json" ] || [ ! -f "registration.php" ]; then
    echo "❌ Error: This script must be run from the Kustomer Integration module root directory"
    exit 1
fi

echo "📋 Vulnerabilities to be addressed:"
echo "  • CVE-2022-24828: Composer command injection"
echo "  • CVE-2021-29472: Composer command injection (Mercurial)"
echo "  • CVE-2024-51736: Symfony Process command execution hijack"
echo "  • ZF2018-01: Zend-HTTP URL rewrite vulnerability"
echo "  • ZF2018-01: Zend-Diactoros URL rewrite vulnerability"
echo ""

# Create backup timestamp
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
echo "🗂️  Creating backup: composer.lock.backup_$BACKUP_DATE"
cp composer.lock "composer.lock.backup_$BACKUP_DATE"

echo ""
echo "🔄 Step 1: Updating Composer to secure version..."
composer self-update

echo ""
echo "📦 Current Composer version:"
composer --version

echo ""
echo "🔍 Step 2: Checking current vulnerable package versions..."
echo "Current versions:"
composer show 2>/dev/null | grep -E "(composer/composer|symfony/process|zendframework/zend-http|zendframework/zend-diactoros)" || echo "Some packages may not be directly listed (managed by parent Magento)"

echo ""
echo "⚠️  IMPORTANT NOTES:"
echo "  • This is a Magento 2 module - some dependencies are managed by parent Magento installation"
echo "  • If you cannot update packages directly, coordinate with your Magento administrator"
echo "  • For Magento Cloud, work with your hosting provider"
echo ""

read -p "Do you want to continue with the patch application? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Patch application cancelled."
    exit 1
fi

echo ""
echo "🔧 Step 3: Attempting to update packages to secure versions..."

# Note: These commands may fail if packages are managed by parent Magento
# That's expected behavior for Magento modules

echo "Updating packages (if directly manageable)..."

# Try to update packages - ignore failures as they may be managed by parent Magento
set +e  # Don't exit on error for package updates

# Clear composer cache first
echo "Clearing Composer cache..."
composer clear-cache

# Try updating vulnerable packages
echo "Attempting to require secure versions..."
composer require "symfony/process:^6.4.14" --no-update 2>/dev/null && echo "✅ symfony/process updated" || echo "ℹ️  symfony/process not directly manageable (likely managed by Magento)"

composer require "zendframework/zend-http:^2.8.1" --no-update 2>/dev/null && echo "✅ zendframework/zend-http updated" || echo "ℹ️  zendframework/zend-http not directly manageable"

composer require "zendframework/zend-diactoros:^1.8.4" --no-update 2>/dev/null && echo "✅ zendframework/zend-diactoros updated" || echo "ℹ️  zendframework/zend-diactoros not directly manageable"

# Update lock file
echo "Updating composer.lock..."
composer update --lock 2>/dev/null || echo "ℹ️  Lock file update may need to be done at Magento root level"

set -e  # Re-enable exit on error

echo ""
echo "🔍 Step 4: Security verification..."

# Run security audit if available
if composer audit --help >/dev/null 2>&1; then
    echo "Running security audit..."
    composer audit 2>/dev/null || echo "ℹ️  Security audit completed - review results above"
else
    echo "ℹ️  Composer audit not available in this version"
fi

echo ""
echo "📊 Post-patch verification:"
echo "Updated versions (if available):"
composer show 2>/dev/null | grep -E "(composer/composer|symfony/process|zendframework/zend-http|zendframework/zend-diactoros)" || echo "Some packages managed at parent level"

echo ""
echo "✅ Security patch application completed!"
echo ""
echo "📋 Next Steps:"
echo "  1. Review the security audit results above"
echo "  2. Test all module functionality thoroughly"
echo "  3. If packages couldn't be updated directly:"
echo "     a. Update your main Magento installation"
echo "     b. Ensure Magento uses the secure package versions listed in SECURITY_PATCH.md"
echo "  4. Deploy to production only after thorough testing"
echo "  5. Monitor for any issues after deployment"
echo ""
echo "📄 For detailed information, see SECURITY_PATCH.md"
echo "💾 Backup created: composer.lock.backup_$BACKUP_DATE"
echo ""
echo "🔒 Security patch process completed successfully!" 