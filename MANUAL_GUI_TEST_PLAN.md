# Manual GUI Frontend Test Plan - MySQL Accounting System

## Overview
This test plan covers comprehensive manual testing of the MySQL-based accounting system frontend. The system includes account management, bank accounts, transactions, journals, and financial reporting capabilities.

## Test Environment Setup
- **Database**: MySQL 8.4
- **Backend**: Laravel 12 with Hilinkz DEAccounting package
- **Frontend**: Laravel Blade/Livewire components
- **Browser**: Chrome, Firefox, Safari, Edge (latest versions)

## Test Data Requirements
Before testing, ensure the following test data is available:
- Company ID: 1
- User accounts with different permission levels
- Chart of accounts with Assets, Liabilities, Equity, Income, Expenses
- Bank accounts linked to chart of accounts
- Sample transactions and journals
- Tax configurations (VAT, Income Tax)

---

## 1. ACCOUNT MANAGEMENT MODULE

### 1.1 Account Types Management

#### Test Case 1.1.1: View Account Types
**Objective**: Verify account types are displayed correctly
**Steps**:
1. Navigate to Accounting > Account Types
2. Verify all account types are listed (Assets, Liabilities, Equity, Income, Expenses)
3. Check that each type shows: ID, Title, Created Date, Updated Date
4. Verify pagination works if more than 10 records exist

**Expected Result**: All account types display with correct information
**Priority**: High

#### Test Case 1.1.2: Create New Account Type
**Objective**: Test creating a new account type
**Steps**:
1. Click "Add New Account Type" button
2. Fill in form fields:
   - Title: "Test Account Type"
3. Click "Save"
4. Verify success message appears
5. Check new account type appears in the list

**Expected Result**: New account type created successfully
**Priority**: High

#### Test Case 1.1.3: Edit Account Type
**Objective**: Test editing existing account type
**Steps**:
1. Click "Edit" button on an existing account type
2. Modify the title to "Updated Account Type"
3. Click "Update"
4. Verify success message appears
5. Check updated information in the list

**Expected Result**: Account type updated successfully
**Priority**: High

#### Test Case 1.1.4: Delete Account Type
**Objective**: Test deleting account type
**Steps**:
1. Click "Delete" button on a test account type
2. Confirm deletion in popup
3. Verify success message appears
4. Check account type removed from list

**Expected Result**: Account type deleted successfully
**Priority**: Medium

### 1.2 Chart of Accounts Management

#### Test Case 1.2.1: View Chart of Accounts
**Objective**: Verify chart of accounts displays in hierarchical structure
**Steps**:
1. Navigate to Accounting > Chart of Accounts
2. Verify accounts display in tree structure
3. Check root accounts (Assets, Liabilities, Equity, Income, Expenses)
4. Verify child accounts are properly nested
5. Test expand/collapse functionality

**Expected Result**: Hierarchical chart of accounts displays correctly
**Priority**: High

#### Test Case 1.2.2: Create New Account
**Objective**: Test creating new account in chart of accounts
**Steps**:
1. Click "Add New Account" button
2. Fill in form fields:
   - Account Number: "1200"
   - Title: "Test Account"
   - Account Type: Select from dropdown
   - Parent Account: Select parent (optional)
   - Financial Statement Placement: "Balance Sheet"
   - Status: "Active"
3. Click "Save"
4. Verify success message appears
5. Check new account appears in correct position in tree

**Expected Result**: New account created and positioned correctly
**Priority**: High

#### Test Case 1.2.3: Edit Account
**Objective**: Test editing existing account
**Steps**:
1. Click "Edit" button on an existing account
2. Modify account title to "Updated Test Account"
3. Change account type if needed
4. Click "Update"
5. Verify success message appears
6. Check updated information in tree

**Expected Result**: Account updated successfully
**Priority**: High

#### Test Case 1.2.4: Move Account (Reorder)
**Objective**: Test moving account to different parent
**Steps**:
1. Select an account in the tree
2. Click "Move" button
3. Select new parent account
4. Click "Move"
5. Verify account appears under new parent
6. Check tree structure is maintained

**Expected Result**: Account moved successfully with proper tree structure
**Priority**: Medium

#### Test Case 1.2.5: Account Validation
**Objective**: Test account number uniqueness and required fields
**Steps**:
1. Try to create account with duplicate account number
2. Try to create account with empty required fields
3. Try to create account with invalid account type
4. Verify appropriate error messages appear

**Expected Result**: Validation errors display correctly
**Priority**: High

---

## 2. BANK MANAGEMENT MODULE

### 2.1 Banks Management

#### Test Case 2.1.1: View Banks
**Objective**: Verify banks list displays correctly
**Steps**:
1. Navigate to Accounting > Banks
2. Verify all banks are listed
3. Check each bank shows: ID, Bank Name, Short Name, Created Date
4. Test search functionality by bank name

**Expected Result**: Banks list displays with search functionality
**Priority**: High

#### Test Case 2.1.2: Create New Bank
**Objective**: Test creating new bank
**Steps**:
1. Click "Add New Bank" button
2. Fill in form fields:
   - Bank Name: "Test Bank Ltd"
   - Short Name: "TBL"
3. Click "Save"
4. Verify success message appears
5. Check new bank appears in list

**Expected Result**: New bank created successfully
**Priority**: High

### 2.2 Bank Accounts Management

#### Test Case 2.2.1: View Bank Accounts
**Objective**: Verify bank accounts list displays correctly
**Steps**:
1. Navigate to Accounting > Bank Accounts
2. Verify all bank accounts are listed
3. Check each account shows: Account ID, Bank, Account Number, Account Name, Branch, Status
4. Test filtering by bank

**Expected Result**: Bank accounts list displays with filtering
**Priority**: High

#### Test Case 2.2.2: Create New Bank Account
**Objective**: Test creating new bank account
**Steps**:
1. Click "Add New Bank Account" button
2. Fill in form fields:
   - Account: Select from chart of accounts
   - Bank: Select from banks list
   - Account Number: "9876543210"
   - Account Name: "Test Business Account"
   - Branch: "Test Branch"
   - Status: "Active"
3. Click "Save"
4. Verify success message appears
5. Check new bank account appears in list

**Expected Result**: New bank account created successfully
**Priority**: High

#### Test Case 2.2.3: Edit Bank Account
**Objective**: Test editing bank account
**Steps**:
1. Click "Edit" button on existing bank account
2. Modify account name to "Updated Business Account"
3. Change branch to "Updated Branch"
4. Click "Update"
5. Verify success message appears
6. Check updated information in list

**Expected Result**: Bank account updated successfully
**Priority**: High

---

## 3. TRANSACTION MANAGEMENT MODULE

### 3.1 Account Transactions

#### Test Case 3.1.1: View Account Transactions
**Objective**: Verify account transactions list displays correctly
**Steps**:
1. Navigate to Accounting > Account Transactions
2. Verify transactions are listed with: Date, Account, Description, Debit, Credit, Balance
3. Test date range filtering
4. Test account filtering
5. Test transaction type filtering

**Expected Result**: Transactions list displays with filtering options
**Priority**: High

#### Test Case 3.1.2: Create New Transaction
**Objective**: Test creating new account transaction
**Steps**:
1. Click "Add New Transaction" button
2. Fill in form fields:
   - Date: Current date
   - Account: Select from chart of accounts
   - Description: "Test Transaction"
   - Amount: "1000.00"
   - Type: "Debit" or "Credit"
   - Note: "Test transaction note"
3. Click "Save"
4. Verify success message appears
5. Check transaction appears in list
6. Verify account balance is updated

**Expected Result**: Transaction created and balance updated
**Priority**: High

#### Test Case 3.1.3: Edit Transaction
**Objective**: Test editing existing transaction
**Steps**:
1. Click "Edit" button on existing transaction
2. Modify amount to "1500.00"
3. Update description to "Updated Test Transaction"
4. Click "Update"
5. Verify success message appears
6. Check updated information in list
7. Verify account balance reflects changes

**Expected Result**: Transaction updated and balance recalculated
**Priority**: High

#### Test Case 3.1.4: Delete Transaction
**Objective**: Test deleting transaction
**Steps**:
1. Click "Delete" button on test transaction
2. Confirm deletion in popup
3. Verify success message appears
4. Check transaction removed from list
5. Verify account balance is adjusted

**Expected Result**: Transaction deleted and balance adjusted
**Priority**: Medium

### 3.2 Journal Entries

#### Test Case 3.2.1: View Journal Entries
**Objective**: Verify journal entries list displays correctly
**Steps**:
1. Navigate to Accounting > Journal Entries
2. Verify journal entries are listed with: Date, Reference, Description, Amount, Status
3. Test date range filtering
4. Test status filtering

**Expected Result**: Journal entries list displays with filtering
**Priority**: High

#### Test Case 3.2.2: Create New Journal Entry
**Objective**: Test creating new journal entry
**Steps**:
1. Click "Add New Journal Entry" button
2. Fill in form fields:
   - Date: Current date
   - Reference: "JE-001"
   - Description: "Test Journal Entry"
   - Transaction Type: "Manual Entry"
3. Add debit line:
   - Account: Select asset account
   - Amount: "1000.00"
4. Add credit line:
   - Account: Select liability account
   - Amount: "1000.00"
5. Click "Save"
6. Verify success message appears
7. Check journal entry appears in list
8. Verify debits equal credits

**Expected Result**: Journal entry created with balanced debits and credits
**Priority**: High

#### Test Case 3.2.3: Edit Journal Entry
**Objective**: Test editing journal entry
**Steps**:
1. Click "Edit" button on existing journal entry
2. Modify description to "Updated Journal Entry"
3. Adjust amounts while maintaining balance
4. Click "Update"
5. Verify success message appears
6. Check updated information in list

**Expected Result**: Journal entry updated successfully
**Priority**: High

#### Test Case 3.2.4: Journal Entry Validation
**Objective**: Test journal entry validation rules
**Steps**:
1. Try to create journal entry with unbalanced debits/credits
2. Try to create journal entry with zero amount
3. Try to create journal entry with invalid accounts
4. Verify appropriate error messages appear

**Expected Result**: Validation errors display correctly
**Priority**: High

---

## 4. ACCOUNT STATEMENTS MODULE

### 4.1 Account Statements

#### Test Case 4.1.1: View Account Statements
**Objective**: Verify account statements display correctly
**Steps**:
1. Navigate to Accounting > Account Statements
2. Select account from dropdown
3. Select date range
4. Click "Generate Statement"
5. Verify statement displays with:
   - Opening balance
   - Transaction details
   - Closing balance
6. Test export to PDF functionality

**Expected Result**: Account statement generates correctly with export option
**Priority**: High

#### Test Case 4.1.2: Account Statement Filtering
**Objective**: Test account statement filtering options
**Steps**:
1. Navigate to Account Statements
2. Test filtering by:
   - Different accounts
   - Different date ranges
   - Transaction types
3. Verify filtered results are accurate

**Expected Result**: Filtering works correctly for all options
**Priority**: Medium

---

## 5. TASKS MANAGEMENT MODULE

### 5.1 Tasks Management

#### Test Case 5.1.1: View Tasks
**Objective**: Verify tasks list displays correctly
**Steps**:
1. Navigate to Accounting > Tasks
2. Verify tasks are listed with: Name, Type, Status, Created Date
3. Test filtering by task type
4. Test filtering by status

**Expected Result**: Tasks list displays with filtering options
**Priority**: Medium

#### Test Case 5.1.2: Create New Task
**Objective**: Test creating new task
**Steps**:
1. Click "Add New Task" button
2. Fill in form fields:
   - Name: "Test Task"
   - Taskable Type: Select type
   - Taskable ID: Enter ID
   - Note: "Test task note"
3. Click "Save"
4. Verify success message appears
5. Check new task appears in list

**Expected Result**: New task created successfully
**Priority**: Medium

---

## 6. FILES MANAGEMENT MODULE

### 6.1 Files Management

#### Test Case 6.1.1: View Files
**Objective**: Verify files list displays correctly
**Steps**:
1. Navigate to Accounting > Files
2. Verify files are listed with: Title, Type, Upload Date, Size
3. Test filtering by file type
4. Test search by title

**Expected Result**: Files list displays with search and filtering
**Priority**: Medium

#### Test Case 6.1.2: Upload File
**Objective**: Test file upload functionality
**Steps**:
1. Click "Upload File" button
2. Select file from computer
3. Enter title for file
4. Select fileable type and ID
5. Click "Upload"
6. Verify success message appears
7. Check file appears in list

**Expected Result**: File uploaded successfully
**Priority**: Medium

#### Test Case 6.1.3: Download File
**Objective**: Test file download functionality
**Steps**:
1. Click "Download" button on existing file
2. Verify file downloads correctly
3. Check file content is intact

**Expected Result**: File downloads correctly
**Priority**: Medium

---

## 7. TAXES MANAGEMENT MODULE

### 7.1 Taxes Management

#### Test Case 7.1.1: View Taxes
**Objective**: Verify taxes list displays correctly
**Steps**:
1. Navigate to Accounting > Taxes
2. Verify taxes are listed with: Name, Rate, Status, Created Date
3. Test filtering by status

**Expected Result**: Taxes list displays with filtering
**Priority**: High

#### Test Case 7.1.2: Create New Tax
**Objective**: Test creating new tax
**Steps**:
1. Click "Add New Tax" button
2. Fill in form fields:
   - Name: "Test Tax"
   - Rate: "10.00"
   - Status: "Active"
3. Click "Save"
4. Verify success message appears
5. Check new tax appears in list

**Expected Result**: New tax created successfully
**Priority**: High

#### Test Case 7.1.3: Edit Tax
**Objective**: Test editing tax
**Steps**:
1. Click "Edit" button on existing tax
2. Modify rate to "12.00"
3. Click "Update"
4. Verify success message appears
5. Check updated information in list

**Expected Result**: Tax updated successfully
**Priority**: High

---

## 8. REPORTING MODULE

### 8.1 Financial Reports

#### Test Case 8.1.1: Trial Balance Report
**Objective**: Test trial balance report generation
**Steps**:
1. Navigate to Reports > Trial Balance
2. Select date range
3. Click "Generate Report"
4. Verify report displays:
   - All accounts with balances
   - Debit and credit totals
   - Balanced totals
5. Test export to PDF/Excel

**Expected Result**: Trial balance report generates correctly
**Priority**: High

#### Test Case 8.1.2: Balance Sheet Report
**Objective**: Test balance sheet report generation
**Steps**:
1. Navigate to Reports > Balance Sheet
2. Select date
3. Click "Generate Report"
4. Verify report displays:
   - Assets section
   - Liabilities section
   - Equity section
   - Balanced totals
5. Test export functionality

**Expected Result**: Balance sheet report generates correctly
**Priority**: High

#### Test Case 8.1.3: Income Statement Report
**Objective**: Test income statement report generation
**Steps**:
1. Navigate to Reports > Income Statement
2. Select date range
3. Click "Generate Report"
4. Verify report displays:
   - Income section
   - Expenses section
   - Net income/loss
5. Test export functionality

**Expected Result**: Income statement report generates correctly
**Priority**: High

#### Test Case 8.1.4: Cash Flow Report
**Objective**: Test cash flow report generation
**Steps**:
1. Navigate to Reports > Cash Flow
2. Select date range
3. Click "Generate Report"
4. Verify report displays:
   - Operating activities
   - Investing activities
   - Financing activities
   - Net cash flow
5. Test export functionality

**Expected Result**: Cash flow report generates correctly
**Priority**: Medium

---

## 9. USER INTERFACE TESTING

### 9.1 Navigation Testing

#### Test Case 9.1.1: Menu Navigation
**Objective**: Test main navigation menu
**Steps**:
1. Click on each main menu item
2. Verify submenu items appear correctly
3. Test navigation between different modules
4. Verify breadcrumb navigation works
5. Test back button functionality

**Expected Result**: Navigation works smoothly across all modules
**Priority**: High

#### Test Case 9.1.2: Responsive Design
**Objective**: Test responsive design on different screen sizes
**Steps**:
1. Test on desktop (1920x1080)
2. Test on tablet (768x1024)
3. Test on mobile (375x667)
4. Verify all elements are accessible
5. Test horizontal scrolling if needed

**Expected Result**: Interface adapts correctly to all screen sizes
**Priority**: Medium

### 9.2 Form Validation Testing

#### Test Case 9.2.1: Required Field Validation
**Objective**: Test required field validation
**Steps**:
1. Try to submit forms with empty required fields
2. Verify error messages appear
3. Check error messages are clear and helpful
4. Test that forms don't submit with validation errors

**Expected Result**: Required field validation works correctly
**Priority**: High

#### Test Case 9.2.2: Data Type Validation
**Objective**: Test data type validation
**Steps**:
1. Enter invalid data types (text in number fields, etc.)
2. Enter data outside valid ranges
3. Enter invalid formats (dates, emails, etc.)
4. Verify appropriate error messages appear

**Expected Result**: Data type validation works correctly
**Priority**: High

---

## 10. PERFORMANCE TESTING

### 10.1 Load Testing

#### Test Case 10.1.1: Large Dataset Performance
**Objective**: Test performance with large datasets
**Steps**:
1. Create 1000+ accounts
2. Create 10000+ transactions
3. Test page load times
4. Test report generation times
5. Test search and filtering performance

**Expected Result**: System performs well with large datasets
**Priority**: Medium

#### Test Case 10.1.2: Concurrent User Testing
**Objective**: Test system with multiple concurrent users
**Steps**:
1. Have 5+ users access system simultaneously
2. Perform various operations (create, edit, delete)
3. Monitor system response times
4. Check for any conflicts or errors

**Expected Result**: System handles concurrent users without issues
**Priority**: Medium

---

## 11. SECURITY TESTING

### 11.1 Access Control Testing

#### Test Case 11.1.1: User Permission Testing
**Objective**: Test user permission restrictions
**Steps**:
1. Login with different user roles
2. Test access to different modules
3. Verify users can only access permitted functions
4. Test unauthorized access attempts

**Expected Result**: User permissions are enforced correctly
**Priority**: High

#### Test Case 11.1.2: Data Security Testing
**Objective**: Test data security measures
**Steps**:
1. Test SQL injection attempts
2. Test XSS (Cross-Site Scripting) attempts
3. Test CSRF (Cross-Site Request Forgery) protection
4. Verify sensitive data is properly protected

**Expected Result**: Security measures protect against common attacks
**Priority**: High

---

## 12. INTEGRATION TESTING

### 12.1 Database Integration Testing

#### Test Case 12.1.1: Data Consistency Testing
**Objective**: Test data consistency across modules
**Steps**:
1. Create transaction in one module
2. Verify data appears correctly in related modules
3. Test data relationships and foreign keys
4. Verify referential integrity

**Expected Result**: Data remains consistent across all modules
**Priority**: High

#### Test Case 12.1.2: Transaction Rollback Testing
**Objective**: Test transaction rollback functionality
**Steps**:
1. Start a complex transaction
2. Intentionally cause an error
3. Verify transaction rolls back completely
4. Check database state is unchanged

**Expected Result**: Transaction rollback works correctly
**Priority**: Medium

---

## 13. BROWSER COMPATIBILITY TESTING

### 13.1 Cross-Browser Testing

#### Test Case 13.1.1: Chrome Testing
**Objective**: Test functionality in Chrome browser
**Steps**:
1. Open system in Chrome (latest version)
2. Test all major functionalities
3. Verify JavaScript works correctly
4. Check CSS rendering

**Expected Result**: All features work correctly in Chrome
**Priority**: High

#### Test Case 13.1.2: Firefox Testing
**Objective**: Test functionality in Firefox browser
**Steps**:
1. Open system in Firefox (latest version)
2. Test all major functionalities
3. Verify JavaScript works correctly
4. Check CSS rendering

**Expected Result**: All features work correctly in Firefox
**Priority**: High

#### Test Case 13.1.3: Safari Testing
**Objective**: Test functionality in Safari browser
**Steps**:
1. Open system in Safari (latest version)
2. Test all major functionalities
3. Verify JavaScript works correctly
4. Check CSS rendering

**Expected Result**: All features work correctly in Safari
**Priority**: Medium

#### Test Case 13.1.4: Edge Testing
**Objective**: Test functionality in Edge browser
**Steps**:
1. Open system in Edge (latest version)
2. Test all major functionalities
3. Verify JavaScript works correctly
4. Check CSS rendering

**Expected Result**: All features work correctly in Edge
**Priority**: Medium

---

## 14. ERROR HANDLING TESTING

### 14.1 Error Message Testing

#### Test Case 14.1.1: User-Friendly Error Messages
**Objective**: Test error message clarity and helpfulness
**Steps**:
1. Trigger various error conditions
2. Verify error messages are clear and actionable
3. Check error messages are not technical jargon
4. Test error message display formatting

**Expected Result**: Error messages are user-friendly and helpful
**Priority**: Medium

#### Test Case 14.1.2: System Error Handling
**Objective**: Test system error handling
**Steps**:
1. Simulate database connection errors
2. Simulate server errors
3. Test network timeout scenarios
4. Verify graceful error handling

**Expected Result**: System handles errors gracefully
**Priority**: High

---

## 15. DATA BACKUP AND RECOVERY TESTING

### 15.1 Backup Testing

#### Test Case 15.1.1: Data Backup Functionality
**Objective**: Test data backup functionality
**Steps**:
1. Create test data in system
2. Perform manual backup
3. Verify backup file is created
4. Check backup file contains all data

**Expected Result**: Backup functionality works correctly
**Priority**: Medium

#### Test Case 15.1.2: Data Recovery Testing
**Objective**: Test data recovery functionality
**Steps**:
1. Restore from backup file
2. Verify all data is restored correctly
3. Test system functionality after recovery
4. Check data integrity

**Expected Result**: Data recovery works correctly
**Priority**: Medium

---

## TEST EXECUTION GUIDELINES

### Pre-Test Setup
1. Ensure MySQL database is running and accessible
2. Verify all required test data is available
3. Clear browser cache and cookies
4. Use fresh browser sessions for each test

### Test Execution
1. Execute tests in the order specified
2. Document all test results (Pass/Fail)
3. Capture screenshots for failed tests
4. Report bugs immediately with detailed information

### Post-Test Cleanup
1. Clean up test data created during testing
2. Reset system to initial state
3. Document any issues found
4. Prepare test summary report

### Test Data Management
- Use consistent test data across all test cases
- Create unique identifiers for test records
- Clean up test data after each test cycle
- Maintain separate test and production databases

### Bug Reporting Template
```
Bug ID: [Unique identifier]
Title: [Brief description]
Priority: [High/Medium/Low]
Severity: [Critical/Major/Minor]
Environment: [Browser, OS, Database version]
Steps to Reproduce:
1. [Step 1]
2. [Step 2]
3. [Step 3]
Expected Result: [What should happen]
Actual Result: [What actually happened]
Screenshots: [If applicable]
```

### Test Completion Criteria
- All High priority test cases must pass
- 90% of Medium priority test cases must pass
- 80% of Low priority test cases must pass
- No Critical severity bugs remain unresolved
- All security-related test cases must pass

---

## CONCLUSION

This comprehensive test plan covers all major aspects of the MySQL-based accounting system frontend. The test cases are designed to ensure the system is robust, user-friendly, and secure. Regular execution of these tests will help maintain system quality and reliability.

**Total Test Cases**: 75+
**Estimated Testing Time**: 40-50 hours
**Recommended Test Team Size**: 2-3 testers
**Test Environment**: Dedicated testing environment with production-like data