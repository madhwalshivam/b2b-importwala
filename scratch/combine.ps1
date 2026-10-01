$files = @("database/coupons_schema.sql", "database/homepage_sections_schema.sql", "database/otp_schema.sql", "database/razorpay_shiprocket_schema.sql", "database/seeders.sql")
foreach ($f in $files) {
    Get-Content $f | Out-File -Append -Encoding UTF8 database/full_import.sql
}
