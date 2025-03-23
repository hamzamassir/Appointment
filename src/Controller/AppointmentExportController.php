<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Drupal\Core\Url;

/**
 * Controller to export Agencies, Advisors, and Appointments as CSV.
 */
class AppointmentExportController extends ControllerBase
{
  /**
   * Exports data as a CSV file.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   The CSV file download response.
   */
    public function exportCsv()
    {
        $response = new StreamedResponse();
        $response->setCallback(function () {
          // Open output stream.
            $handle = fopen('php://output', 'w');

          // Write CSV header.
            fputcsv($handle, ['Entity Type', 'ID', 'Name/Title', 'Details']);

          // --- Export Agencies ---
            $agency_storage = \Drupal::entityTypeManager()->getStorage('agency');
            $agencies = $agency_storage->loadMultiple();
            foreach ($agencies as $agency) {
                fputcsv($handle, [
                'Agency',
                $agency->id(),
                $agency->label(),
                'Address: ' . $agency->get('address')->value . '; Phone: ' . $agency->get('phone')->value,
                ]);
            }

          // --- Export Advisors (users with role 'adviser') ---
            $advisor_storage = \Drupal::entityTypeManager()->getStorage('user');
            $query = $advisor_storage->getQuery()
            ->condition('status', 1)
            ->condition('roles', 'adviser')
            ->accessCheck(false);
            $advisor_ids = $query->execute();
            $advisers = $advisor_storage->loadMultiple($advisor_ids);
            foreach ($advisers as $advisor) {
                fputcsv($handle, [
                'Adviser',
                $advisor->id(),
                $advisor->getDisplayName(),
                'Email: ' . $advisor->getEmail(),
                ]);
            }

          // --- Export Appointments ---
            $appointment_storage = \Drupal::entityTypeManager()->getStorage('appointment');
            $appointments = $appointment_storage->loadMultiple();
            foreach ($appointments as $appointment) {
                $details = 'Adviser: ' . $appointment->get('adviser')->target_id .
                   '; Date: ' . $appointment->get('appointment_date')->value .
                   '; Time: ' . $appointment->get('time_slot')->value;
                fputcsv($handle, [
                'Appointment',
                $appointment->id(),
                $appointment->get('title')->value,
                $details,
                ]);
            }
            fclose($handle);
        });

      // Set headers to prompt download.
        $filename = 'appointment_export_' . date('Y-m-d_H-i-s') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
        return $response;
    }
}
