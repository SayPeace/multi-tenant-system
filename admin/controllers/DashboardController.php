<?php
/**
 * Dashboard Controller
 * Handles main dashboard display for all user types
 */

class DashboardController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Main dashboard - redirects based on role
     */
    public function index(): void
    {
        if (AdminAuth::isSuperAdmin()) {
            header('Location: ' . $this->baseUrl . '/superadmin/dashboard');
        } elseif (AdminAuth::isEditorInChief()) {
            header('Location: ' . $this->baseUrl . '/editor/dashboard');
        } elseif (AdminAuth::isAuthor()) {
            header('Location: ' . $this->baseUrl . '/author/dashboard');
        } elseif (AdminAuth::isReviewer()) {
            header('Location: ' . $this->baseUrl . '/reviewer/dashboard');
        } else {
            Flash::error('Unknown user role.');
            header('Location: ' . $this->baseUrl . '/login');
        }
        exit;
    }
}
