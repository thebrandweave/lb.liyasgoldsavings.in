-- ====================================================================
-- DATABASE REPAIR SCRIPT FOR LIYAS GOLD SAVINGS
-- Fixes missing PRIMARY KEY and AUTO_INCREMENT attributes on tables
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. FIX CUSTOMERS TABLE
-- Update any duplicate/default 0 ID to a new safe ID
SET @max_cust_id = (SELECT IFNULL(MAX(CustomerID), 2331) FROM Customers WHERE CustomerID > 0);
UPDATE Customers SET CustomerID = @max_cust_id + 1 WHERE CustomerID = 0;
-- Add primary key constraint and auto increment
ALTER TABLE Customers ADD PRIMARY KEY (CustomerID);
ALTER TABLE Customers MODIFY CustomerID INT AUTO_INCREMENT;
SET @next_cust_auto = (SELECT IFNULL(MAX(CustomerID), 0) + 1 FROM Customers);
SET @sql_cust = CONCAT('ALTER TABLE Customers AUTO_INCREMENT = ', @next_cust_auto);
PREPARE stmt_cust FROM @sql_cust;
EXECUTE stmt_cust;
DEALLOCATE PREPARE stmt_cust;

-- 2. FIX PROMOTERS TABLE
SET @max_prom_id = (SELECT IFNULL(MAX(PromoterID), 2510) FROM Promoters WHERE PromoterID > 0);
UPDATE Promoters SET PromoterID = @max_prom_id + 1 WHERE PromoterID = 0;
ALTER TABLE Promoters ADD PRIMARY KEY (PromoterID);
ALTER TABLE Promoters MODIFY PromoterID INT AUTO_INCREMENT;
SET @next_prom_auto = (SELECT IFNULL(MAX(PromoterID), 0) + 1 FROM Promoters);
SET @sql_prom = CONCAT('ALTER TABLE Promoters AUTO_INCREMENT = ', @next_prom_auto);
PREPARE stmt_prom FROM @sql_prom;
EXECUTE stmt_prom;
DEALLOCATE PREPARE stmt_prom;

-- 3. FIX PAYMENTS TABLE
SET @max_pay_id = (SELECT IFNULL(MAX(PaymentID), 21085) FROM Payments WHERE PaymentID > 0);
UPDATE Payments SET PaymentID = @max_pay_id + 1 WHERE PaymentID = 0;
ALTER TABLE Payments ADD PRIMARY KEY (PaymentID);
ALTER TABLE Payments MODIFY PaymentID INT AUTO_INCREMENT;
SET @next_pay_auto = (SELECT IFNULL(MAX(PaymentID), 0) + 1 FROM Payments);
SET @sql_pay = CONCAT('ALTER TABLE Payments AUTO_INCREMENT = ', @next_pay_auto);
PREPARE stmt_pay FROM @sql_pay;
EXECUTE stmt_pay;
DEALLOCATE PREPARE stmt_pay;

-- 4. FIX ACTIVITYLOGS TABLE
SET @max_act_id = (SELECT IFNULL(MAX(LogID), 35355) FROM ActivityLogs WHERE LogID > 0);
UPDATE ActivityLogs SET LogID = @max_act_id + 1 WHERE LogID = 0;
ALTER TABLE ActivityLogs ADD PRIMARY KEY (LogID);
ALTER TABLE ActivityLogs MODIFY LogID INT AUTO_INCREMENT;
SET @next_act_auto = (SELECT IFNULL(MAX(LogID), 0) + 1 FROM ActivityLogs);
SET @sql_act = CONCAT('ALTER TABLE ActivityLogs AUTO_INCREMENT = ', @next_act_auto);
PREPARE stmt_act FROM @sql_act;
EXECUTE stmt_act;
DEALLOCATE PREPARE stmt_act;

-- 5. FIX INSTALLMENTS TABLE
SET @max_inst_id = (SELECT IFNULL(MAX(InstallmentID), 76) FROM Installments WHERE InstallmentID > 0);
UPDATE Installments SET InstallmentID = @max_inst_id + 1 WHERE InstallmentID = 0;
ALTER TABLE Installments ADD PRIMARY KEY (InstallmentID);
ALTER TABLE Installments MODIFY InstallmentID INT AUTO_INCREMENT;
SET @next_inst_auto = (SELECT IFNULL(MAX(InstallmentID), 0) + 1 FROM Installments);
SET @sql_inst = CONCAT('ALTER TABLE Installments AUTO_INCREMENT = ', @next_inst_auto);
PREPARE stmt_inst FROM @sql_inst;
EXECUTE stmt_inst;
DEALLOCATE PREPARE stmt_inst;

-- 6. FIX KYC TABLE
SET @max_kyc_id = (SELECT IFNULL(MAX(KYCID), 288) FROM KYC WHERE KYCID > 0);
UPDATE KYC SET KYCID = @max_kyc_id + 1 WHERE KYCID = 0;
ALTER TABLE KYC ADD PRIMARY KEY (KYCID);
ALTER TABLE KYC MODIFY KYCID INT AUTO_INCREMENT;
SET @next_kyc_auto = (SELECT IFNULL(MAX(KYCID), 0) + 1 FROM KYC);
SET @sql_kyc = CONCAT('ALTER TABLE KYC AUTO_INCREMENT = ', @next_kyc_auto);
PREPARE stmt_kyc FROM @sql_kyc;
EXECUTE stmt_kyc;
DEALLOCATE PREPARE stmt_kyc;

-- 7. FIX SUBSCRIPTIONS TABLE
SET @max_sub_id = (SELECT IFNULL(MAX(SubscriptionID), 10169) FROM Subscriptions WHERE SubscriptionID > 0);
UPDATE Subscriptions SET SubscriptionID = @max_sub_id + 1 WHERE SubscriptionID = 0;
ALTER TABLE Subscriptions ADD PRIMARY KEY (SubscriptionID);
ALTER TABLE Subscriptions MODIFY SubscriptionID INT AUTO_INCREMENT;
SET @next_sub_auto = (SELECT IFNULL(MAX(SubscriptionID), 0) + 1 FROM Subscriptions);
SET @sql_sub = CONCAT('ALTER TABLE Subscriptions AUTO_INCREMENT = ', @next_sub_auto);
PREPARE stmt_sub FROM @sql_sub;
EXECUTE stmt_sub;
DEALLOCATE PREPARE stmt_sub;

-- 8. FIX WITHDRAWALS TABLE
SET @max_wth_id = (SELECT IFNULL(MAX(WithdrawalID), 873) FROM Withdrawals WHERE WithdrawalID > 0);
UPDATE Withdrawals SET WithdrawalID = @max_wth_id + 1 WHERE WithdrawalID = 0;
ALTER TABLE Withdrawals ADD PRIMARY KEY (WithdrawalID);
ALTER TABLE Withdrawals MODIFY WithdrawalID INT AUTO_INCREMENT;
SET @next_wth_auto = (SELECT IFNULL(MAX(WithdrawalID), 0) + 1 FROM Withdrawals);
SET @sql_wth = CONCAT('ALTER TABLE Withdrawals AUTO_INCREMENT = ', @next_wth_auto);
PREPARE stmt_wth FROM @sql_wth;
EXECUTE stmt_wth;
DEALLOCATE PREPARE stmt_wth;

-- 9. FIX WHATSAPPQUEUE TABLE
SET @max_wa_id = (SELECT IFNULL(MAX(QueueID), 470) FROM WhatsAppQueue WHERE QueueID > 0);
UPDATE WhatsAppQueue SET QueueID = @max_wa_id + 1 WHERE QueueID = 0;
ALTER TABLE WhatsAppQueue ADD PRIMARY KEY (QueueID);
ALTER TABLE WhatsAppQueue MODIFY QueueID INT AUTO_INCREMENT;
SET @next_wa_auto = (SELECT IFNULL(MAX(QueueID), 0) + 1 FROM WhatsAppQueue);
SET @sql_wa = CONCAT('ALTER TABLE WhatsAppQueue AUTO_INCREMENT = ', @next_wa_auto);
PREPARE stmt_wa FROM @sql_wa;
EXECUTE stmt_wa;
DEALLOCATE PREPARE stmt_wa;

-- 10. FIX PAYMENT CODES PER MONTH
SET @max_pc_id = (SELECT IFNULL(MAX(RecordID), 0) FROM PaymentCodesPerMonth WHERE RecordID > 0);
UPDATE PaymentCodesPerMonth SET RecordID = @max_pc_id + 1 WHERE RecordID = 0;
ALTER TABLE PaymentCodesPerMonth ADD PRIMARY KEY (RecordID);
ALTER TABLE PaymentCodesPerMonth MODIFY RecordID INT AUTO_INCREMENT;
SET @next_pc_auto = (SELECT IFNULL(MAX(RecordID), 0) + 1 FROM PaymentCodesPerMonth);
SET @sql_pc = CONCAT('ALTER TABLE PaymentCodesPerMonth AUTO_INCREMENT = ', @next_pc_auto);
PREPARE stmt_pc FROM @sql_pc;
EXECUTE stmt_pc;
DEALLOCATE PREPARE stmt_pc;

-- 11. FIX NOTIFICATIONS TABLE
SET @max_not_id = (SELECT IFNULL(MAX(NotificationID), 20912) FROM Notifications WHERE NotificationID > 0);
UPDATE Notifications SET NotificationID = @max_not_id + 1 WHERE NotificationID = 0;
ALTER TABLE Notifications ADD PRIMARY KEY (NotificationID);
ALTER TABLE Notifications MODIFY NotificationID INT AUTO_INCREMENT;
SET @next_not_auto = (SELECT IFNULL(MAX(NotificationID), 0) + 1 FROM Notifications);
SET @sql_not = CONCAT('ALTER TABLE Notifications AUTO_INCREMENT = ', @next_not_auto);
PREPARE stmt_not FROM @sql_not;
EXECUTE stmt_not;
DEALLOCATE PREPARE stmt_not;

-- 12. FIX WHATSAPPAPICONFIG TABLE
SET @max_wac_id = (SELECT IFNULL(MAX(ConfigID), 2) FROM WhatsAppAPIConfig WHERE ConfigID > 0);
UPDATE WhatsAppAPIConfig SET ConfigID = @max_wac_id + 1 WHERE ConfigID = 0;
ALTER TABLE WhatsAppAPIConfig ADD PRIMARY KEY (ConfigID);
ALTER TABLE WhatsAppAPIConfig MODIFY ConfigID INT AUTO_INCREMENT;
SET @next_wac_auto = (SELECT IFNULL(MAX(ConfigID), 0) + 1 FROM WhatsAppAPIConfig);
SET @sql_wac = CONCAT('ALTER TABLE WhatsAppAPIConfig AUTO_INCREMENT = ', @next_wac_auto);
PREPARE stmt_wac FROM @sql_wac;
EXECUTE stmt_wac;
DEALLOCATE PREPARE stmt_wac;

-- 13. FIX NOTIFICATIONCHANNELSETTINGS TABLE
SET @max_ncs_id = (SELECT IFNULL(MAX(SettingID), 2) FROM NotificationChannelSettings WHERE SettingID > 0);
UPDATE NotificationChannelSettings SET SettingID = @max_ncs_id + 1 WHERE SettingID = 0;
ALTER TABLE NotificationChannelSettings ADD PRIMARY KEY (SettingID);
ALTER TABLE NotificationChannelSettings MODIFY SettingID INT AUTO_INCREMENT;
SET @next_ncs_auto = (SELECT IFNULL(MAX(SettingID), 0) + 1 FROM NotificationChannelSettings);
SET @sql_ncs = CONCAT('ALTER TABLE NotificationChannelSettings AUTO_INCREMENT = ', @next_ncs_auto);
PREPARE stmt_ncs FROM @sql_ncs;
EXECUTE stmt_ncs;
DEALLOCATE PREPARE stmt_ncs;

-- 14. FIX REMEMBERTOKENS TABLE
SET @max_rt_id = (SELECT IFNULL(MAX(id), 3) FROM RememberTokens WHERE id > 0);
UPDATE RememberTokens SET id = @max_rt_id + 1 WHERE id = 0;
ALTER TABLE RememberTokens ADD PRIMARY KEY (id);
ALTER TABLE RememberTokens MODIFY id INT AUTO_INCREMENT;
SET @next_rt_auto = (SELECT IFNULL(MAX(id), 0) + 1 FROM RememberTokens);
SET @sql_rt = CONCAT('ALTER TABLE RememberTokens AUTO_INCREMENT = ', @next_rt_auto);
PREPARE stmt_rt FROM @sql_rt;
EXECUTE stmt_rt;
DEALLOCATE PREPARE stmt_rt;

-- 15. FIX TEAMS TABLE
SET @max_tm_id = (SELECT IFNULL(MAX(TeamID), 0) FROM Teams WHERE TeamID > 0);
UPDATE Teams SET TeamID = @max_tm_id + 1 WHERE TeamID = 0;
ALTER TABLE Teams ADD PRIMARY KEY (TeamID);
ALTER TABLE Teams MODIFY TeamID INT AUTO_INCREMENT;
SET @next_tm_auto = (SELECT IFNULL(MAX(TeamID), 0) + 1 FROM Teams);
SET @sql_tm = CONCAT('ALTER TABLE Teams AUTO_INCREMENT = ', @next_tm_auto);
PREPARE stmt_tm FROM @sql_tm;
EXECUTE stmt_tm;
DEALLOCATE PREPARE stmt_tm;

-- 16. FIX WINNERS TABLE
SET @max_wn_id = (SELECT IFNULL(MAX(WinnerID), 0) FROM Winners WHERE WinnerID > 0);
UPDATE Winners SET WinnerID = @max_wn_id + 1 WHERE WinnerID = 0;
ALTER TABLE Winners ADD PRIMARY KEY (WinnerID);
ALTER TABLE Winners MODIFY WinnerID INT AUTO_INCREMENT;
SET @next_wn_auto = (SELECT IFNULL(MAX(WinnerID), 0) + 1 FROM Winners);
SET @sql_wn = CONCAT('ALTER TABLE Winners AUTO_INCREMENT = ', @next_wn_auto);
PREPARE stmt_wn FROM @sql_wn;
EXECUTE stmt_wn;
DEALLOCATE PREPARE stmt_wn;

-- 17. FIX BALANCES TABLE
SET @max_bal_id = (SELECT IFNULL(MAX(BalanceID), 0) FROM Balances WHERE BalanceID > 0);
UPDATE Balances SET BalanceID = @max_bal_id + 1 WHERE BalanceID = 0;
ALTER TABLE Balances ADD PRIMARY KEY (BalanceID);
ALTER TABLE Balances MODIFY BalanceID INT AUTO_INCREMENT;
SET @next_bal_auto = (SELECT IFNULL(MAX(BalanceID), 0) + 1 FROM Balances);
SET @sql_bal = CONCAT('ALTER TABLE Balances AUTO_INCREMENT = ', @next_bal_auto);
PREPARE stmt_bal FROM @sql_bal;
EXECUTE stmt_bal;
DEALLOCATE PREPARE stmt_bal;

-- 18. FIX PROMOTERWALLET TABLE
SET @max_pwal_id = (SELECT IFNULL(MAX(BalanceID), 1251) FROM PromoterWallet WHERE BalanceID > 0);
UPDATE PromoterWallet SET BalanceID = @max_pwal_id + 1 WHERE BalanceID = 0;
ALTER TABLE PromoterWallet ADD PRIMARY KEY (BalanceID);
ALTER TABLE PromoterWallet MODIFY BalanceID INT AUTO_INCREMENT;
SET @next_pwal_auto = (SELECT IFNULL(MAX(BalanceID), 0) + 1 FROM PromoterWallet);
SET @sql_pwal = CONCAT('ALTER TABLE PromoterWallet AUTO_INCREMENT = ', @next_pwal_auto);
PREPARE stmt_pwal FROM @sql_pwal;
EXECUTE stmt_pwal;
DEALLOCATE PREPARE stmt_pwal;

-- 19. FIX WALLETLOGS TABLE
SET @max_wlog_id = (SELECT IFNULL(MAX(LogID), 35355) FROM WalletLogs WHERE LogID > 0);
UPDATE WalletLogs SET LogID = @max_wlog_id + 1 WHERE LogID = 0;
ALTER TABLE WalletLogs ADD PRIMARY KEY (LogID);
ALTER TABLE WalletLogs MODIFY LogID INT AUTO_INCREMENT;
SET @next_wlog_auto = (SELECT IFNULL(MAX(LogID), 0) + 1 FROM WalletLogs);
SET @sql_wlog = CONCAT('ALTER TABLE WalletLogs AUTO_INCREMENT = ', @next_wlog_auto);
PREPARE stmt_wlog FROM @sql_wlog;
EXECUTE stmt_wlog;
DEALLOCATE PREPARE stmt_wlog;

-- 20. FIX SCHEMES TABLE
SET @max_sch_id = (SELECT IFNULL(MAX(SchemeID), 4) FROM Schemes WHERE SchemeID > 0);
UPDATE Schemes SET SchemeID = @max_sch_id + 1 WHERE SchemeID = 0;
ALTER TABLE Schemes ADD PRIMARY KEY (SchemeID);
ALTER TABLE Schemes MODIFY SchemeID INT AUTO_INCREMENT;
SET @next_sch_auto = (SELECT IFNULL(MAX(SchemeID), 0) + 1 FROM Schemes);
SET @sql_sch = CONCAT('ALTER TABLE Schemes AUTO_INCREMENT = ', @next_sch_auto);
PREPARE stmt_sch FROM @sql_sch;
EXECUTE stmt_sch;
DEALLOCATE PREPARE stmt_sch;

-- 21. FIX SMSAPICONFIG TABLE
SET @max_sms_id = (SELECT IFNULL(MAX(ConfigID), 1) FROM SMSAPIConfig WHERE ConfigID > 0);
UPDATE SMSAPIConfig SET ConfigID = @max_sms_id + 1 WHERE ConfigID = 0;
ALTER TABLE SMSAPIConfig ADD PRIMARY KEY (ConfigID);
ALTER TABLE SMSAPIConfig MODIFY ConfigID INT AUTO_INCREMENT;
SET @next_sms_auto = (SELECT IFNULL(MAX(ConfigID), 0) + 1 FROM SMSAPIConfig);
SET @sql_sms = CONCAT('ALTER TABLE SMSAPIConfig AUTO_INCREMENT = ', @next_sms_auto);
PREPARE stmt_sms FROM @sql_sms;
EXECUTE stmt_sms;
DEALLOCATE PREPARE stmt_sms;

-- 22. FIX MESSAGINGPREFERENCE TABLE
SET @max_mp_id = (SELECT IFNULL(MAX(PreferenceID), 0) FROM MessagingPreference WHERE PreferenceID > 0);
UPDATE MessagingPreference SET PreferenceID = @max_mp_id + 1 WHERE PreferenceID = 0;
ALTER TABLE MessagingPreference ADD PRIMARY KEY (PreferenceID);
ALTER TABLE MessagingPreference MODIFY PreferenceID INT AUTO_INCREMENT;
SET @next_mp_auto = (SELECT IFNULL(MAX(PreferenceID), 0) + 1 FROM MessagingPreference);
SET @sql_mp = CONCAT('ALTER TABLE MessagingPreference AUTO_INCREMENT = ', @next_mp_auto);
PREPARE stmt_mp FROM @sql_mp;
EXECUTE stmt_mp;
DEALLOCATE PREPARE stmt_mp;

-- 23. FIX CUSTOMERUNIQUECOUNTER TABLE
SET @max_cuc_id = (SELECT IFNULL(MAX(id), 0) FROM CustomerUniqueCounter WHERE id > 0);
UPDATE CustomerUniqueCounter SET id = @max_cuc_id + 1 WHERE id = 0;
ALTER TABLE CustomerUniqueCounter ADD PRIMARY KEY (id);
ALTER TABLE CustomerUniqueCounter MODIFY id INT AUTO_INCREMENT;
SET @next_cuc_auto = (SELECT IFNULL(MAX(id), 0) + 1 FROM CustomerUniqueCounter);
SET @sql_cuc = CONCAT('ALTER TABLE CustomerUniqueCounter AUTO_INCREMENT = ', @next_cuc_auto);
PREPARE stmt_cuc FROM @sql_cuc;
EXECUTE stmt_cuc;
DEALLOCATE PREPARE stmt_cuc;

SET FOREIGN_KEY_CHECKS = 1;
