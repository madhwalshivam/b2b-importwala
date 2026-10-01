$files = @("create_collection_cards.sql", "rfq.sql", "visual_embeddings_schema.sql", "visual_signatures.sql")
foreach ($f in $files) {
    cmd /c "c:\xampp\mysql\bin\mysql.exe -u root importwala < database\migrations\$f"
}
