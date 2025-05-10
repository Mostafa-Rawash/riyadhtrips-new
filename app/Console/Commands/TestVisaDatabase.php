<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestVisaDatabase extends Command
{
    protected $signature = 'test:visa-database';
    protected $description = 'Test the visa external database connection';

    public function handle()
    {
        try {
            $this->info('Testing visa external database connection...');
            
            // Test connection
            DB::connection('visa_external')->getPdo();
            $this->info('✓ Connection successful!');
            
            // Test a simple query
            $tables = DB::connection('visa_external')->select('SHOW TABLES');
            $this->info('✓ Query successful!');
            
            $this->info('Tables found: ' . count($tables));
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $this->line('- ' . $tableName);
            }
            
            // Check visa_application_summary table
            $visaTable = DB::connection('visa_external')->select('SHOW TABLES LIKE "visa_application_summary"');
            if (count($visaTable) > 0) {
                $this->info('✓ visa_application_summary table exists!');
                
                $count = DB::connection('visa_external')->table('visa_application_summary')->count();
                $this->info("Records in visa_application_summary: $count");
            } else {
                $this->error('✗ visa_application_summary table not found!');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Connection failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Code: ' . $e->getCode());
            
            if ($e->getCode() == 1045) {
                $this->line('');
                $this->line('Access denied error (1045) - possible solutions:');
                $this->line('1. Check if password contains special characters that need escaping');
                $this->line('2. Verify password in .env file matches MySQL');
                $this->line('3. Reset the password: ALTER USER \'riyaoeiu_subvisadom\'@\'127.0.0.1\' IDENTIFIED BY \'new_password\';');
            }
            
            return 1;
        }
    }
}
