<?php
/* Caregiver helpers shared by the consultation form and the patient booking form. */

if (!function_exists('available_caregivers')) {
    /* Caregivers that patients may currently choose, with their specialization. */
    function available_caregivers(mysqli $conn): array {
        try {
            return $conn->query(
                "SELECT id, name, specialization, bio, years_experience
                 FROM caregivers
                 WHERE is_available = 1
                 ORDER BY name"
            )->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $ex) {
            error_log('Caregiver list failed: ' . $ex->getMessage());
            return [];
        }
    }
}

if (!function_exists('caregiver_is_available')) {
    /* True when $id (> 0) is one of the caregivers returned by available_caregivers(). */
    function caregiver_is_available(array $caregivers, int $id): bool {
        return in_array($id, array_map('intval', array_column($caregivers, 'id')), true);
    }
}