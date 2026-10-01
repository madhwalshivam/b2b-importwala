$files = @("coupons_schema.sql", "otp_schema.sql", "razorpay_shiprocket_schema.sql")
foreach ($f in $files) {
    Get-Content "database\$f" | Set-Content "database\$($f)_utf8.sql" -Encoding UTF8
    cmd /c "c:\xampp\mysql\bin\mysql.exe -u root importwala < database\$($f)_utf8.sql"
}
