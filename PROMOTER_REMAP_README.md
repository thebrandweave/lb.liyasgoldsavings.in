# Re-mapping Customers to Correct Promoters – Data Sources

## Problem
All customers in `Customers` table had their `PromoterID` set to one promoter (e.g. GDP01562). We need to restore the correct promoter for each customer.

## What Can Be Used (from your dataoutputs)

### 1. **WalletLogs** (best source)
- **Table:** `WalletLogs`  
  Columns: `LogID`, `PromoterUniqueID`, `Amount`, `Message`, `CreatedAt`, `TransactionType`
- **Why it works:** When a payment is verified, commission is credited and a log entry is written with the **direct promoter** and a message like:
  - `"Initial wallet creation with commission from customer Salma siddiqha (LA01022) for Gold Savings Plan (LA) scheme"`
  - `"Commission earned from customer MD Farhan (LA01021) for Gold Savings Plan (LA) scheme"`
- **Rule:** Use rows where the message contains **commission from customer** or **Commission earned from customer**, and **does NOT** contain **Parent commission** (so we get the direct promoter, not the parent).
- **Mapping:** From each such message we extract `CustomerUniqueID` (e.g. LA01022) and set `Customers.PromoterID = WalletLogs.PromoterUniqueID` for that customer.

### 2. **ActivityLogs** (fallback for registered-but-no-payment customers)
- **Table:** `ActivityLogs`  
  Columns: `LogID`, `UserID`, `UserType`, `Action`, `IPAddress`, `CreatedAt`
- **Why it works:** When a promoter registers a customer, an action is logged like:
  - `"Registered new customer: muhammad azlan (ID: GDE01)"` with `UserID = 527` and `UserType = 'Promoter'`.
- **Mapping:** `UserID` in ActivityLogs (when UserType = 'Promoter') is the integer `PromoterID` in the `Promoters` table. We get `PromoterUniqueID` from `Promoters` where `PromoterID = ActivityLogs.UserID`. We extract the customer ID from the action text (e.g. GDE01, LA02) and update that customer’s `PromoterID`.
- **Use for:** Customers who have no payment yet (so no WalletLogs entry). Script uses WalletLogs first, then fills remaining customers from ActivityLogs.

### 3. **Not usable for this mapping**
- **Payments:** In your sample, `PromoterID` is NULL, so it doesn’t store who the promoter was.
- **Subscriptions:** No promoter column.
- **Customers.TeamName:** Same for many (e.g. ROYAL DIAMOND); not a unique promoter identifier.
- **PromoterWallet:** Has promoter and message, but the same info is in WalletLogs in a clearer form.

## Script
Run `remap_customers_promoters.php`:
- **Dry run (default):** `http://localhost/la.goldendream.in/remap_customers_promoters.php`  
  Shows which customers would be updated and from which source (WalletLogs vs ActivityLogs). No DB writes.
- **Apply updates:** `http://localhost/la.goldendream.in/remap_customers_promoters.php?run=1`  
  Performs the `UPDATE Customers SET PromoterID = ...` for each mapped customer.

After running, remove the script and this README if you no longer need them.
