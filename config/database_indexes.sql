-- ====================================================================
-- LIYAS GOLD SAVINGS - COMPLETE PRIMARY KEYS, AUTO_INCREMENT & INDEXES
-- Run this script in phpMyAdmin after importing a raw/unindexed database
-- ====================================================================

-- 1. PRIMARY KEYS & AUTO_INCREMENT
ALTER TABLE `customers` MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`CustomerID`);
ALTER TABLE `payments` MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`PaymentID`);
ALTER TABLE `subscriptions` MODIFY `SubscriptionID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`SubscriptionID`);
ALTER TABLE `promoters` MODIFY `PromoterID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`PromoterID`);
ALTER TABLE `installments` MODIFY `InstallmentID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`InstallmentID`);
ALTER TABLE `schemes` MODIFY `SchemeID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`SchemeID`);
ALTER TABLE `admins` MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`AdminID`);
ALTER TABLE `activitylogs` MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `apilogs` MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `teams` MODIFY `TeamID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`TeamID`);
ALTER TABLE `withdrawals` MODIFY `WithdrawalID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`WithdrawalID`);
ALTER TABLE `winners` MODIFY `WinnerID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`WinnerID`);
ALTER TABLE `notifications` MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`NotificationID`);
ALTER TABLE `kyc` MODIFY `KYCID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`KYCID`);
ALTER TABLE `careeropenings` MODIFY `OpeningID` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`OpeningID`);
ALTER TABLE `careerapplications` MODIFY `ApplicationID` int(11) NOT NULL AUTO_INCREMENT;

-- 2. HIGH-PERFORMANCE QUERY INDEXES

-- Customers
ALTER TABLE `customers` ADD INDEX `idx_cust_unique` (`CustomerUniqueID`);
ALTER TABLE `customers` ADD INDEX `idx_cust_promoter` (`PromoterID`);
ALTER TABLE `customers` ADD INDEX `idx_cust_status` (`Status`);
ALTER TABLE `customers` ADD INDEX `idx_cust_contact` (`Contact`);
ALTER TABLE `customers` ADD INDEX `idx_cust_email` (`Email`);

-- Payments
ALTER TABLE `payments` ADD INDEX `idx_pay_cust_inst_status` (`CustomerID`, `InstallmentID`, `Status`);
ALTER TABLE `payments` ADD INDEX `idx_pay_cust` (`CustomerID`);
ALTER TABLE `payments` ADD INDEX `idx_pay_inst` (`InstallmentID`);
ALTER TABLE `payments` ADD INDEX `idx_pay_scheme` (`SchemeID`);
ALTER TABLE `payments` ADD INDEX `idx_pay_promoter` (`PromoterID`);
ALTER TABLE `payments` ADD INDEX `idx_pay_status` (`Status`);
ALTER TABLE `payments` ADD INDEX `idx_pay_utr_status` (`UTRNumber`, `Status`);

-- Subscriptions
ALTER TABLE `subscriptions` ADD INDEX `idx_sub_cust_scheme` (`CustomerID`, `SchemeID`);
ALTER TABLE `subscriptions` ADD INDEX `idx_sub_cust` (`CustomerID`);
ALTER TABLE `subscriptions` ADD INDEX `idx_sub_scheme` (`SchemeID`);
ALTER TABLE `subscriptions` ADD INDEX `idx_sub_renewal` (`RenewalStatus`);

-- Promoters
ALTER TABLE `promoters` ADD INDEX `idx_promoter_unique` (`PromoterUniqueID`);
ALTER TABLE `promoters` ADD INDEX `idx_promoter_contact` (`Contact`);
ALTER TABLE `promoters` ADD INDEX `idx_promoter_email` (`Email`);
ALTER TABLE `promoters` ADD INDEX `idx_promoter_status` (`Status`);

-- Installments
ALTER TABLE `installments` ADD INDEX `idx_inst_scheme` (`SchemeID`);
ALTER TABLE `installments` ADD INDEX `idx_inst_status` (`Status`);

-- Schemes
ALTER TABLE `schemes` ADD INDEX `idx_scheme_status` (`Status`);

-- ActivityLogs
ALTER TABLE `activitylogs` ADD INDEX `idx_act_user_type_created` (`UserID`, `UserType`, `CreatedAt`);

-- Notifications
ALTER TABLE `notifications` ADD INDEX `idx_notif_user_type` (`UserID`, `UserType`);

-- Teams
ALTER TABLE `teams` ADD INDEX `idx_team_unique` (`TeamUniqueID`);

-- Withdrawals
ALTER TABLE `withdrawals` ADD INDEX `idx_with_user_type` (`UserID`, `UserType`);
