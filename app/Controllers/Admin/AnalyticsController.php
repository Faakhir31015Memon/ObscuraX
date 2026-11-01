<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Stat;

final class AnalyticsController extends Controller
{
    public function index(): void
    {
        $months = in_array((int) ($_GET['months'] ?? 12), [3, 6, 12, 24], true) ? (int) ($_GET['months'] ?? 12) : 12;

        $this->view('admin/analytics', [
            'pageTitle' => 'Analytics — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Platform analytics and usage insights.',
            'bodyClass' => 'page-admin-analytics',
            'charts' => true,
            'months' => $months,
            'overview' => Stat::overview(),
            'monthly' => Stat::monthlySeries($months),
            'yearly' => Stat::yearlySeries(5),
            'viewsByDay' => Stat::viewsByDay(30),
            'topViewed' => Stat::topBlogs('views', 8),
            'topLiked' => Stat::topBlogs('likes', 8),
            'topCommented' => Stat::topBlogs('comments', 8),
            'topUsers' => Stat::topUsers(10),
            'recentRegistrations' => Stat::recentRegistrations(10),
            'usersByStatus' => Stat::usersByStatus(),
            'categories' => $this->categoryStats(),
            'roles' => Database::fetchAll(
                'SELECT r.name, r.slug, COUNT(u.id) AS total
                 FROM roles r LEFT JOIN users u ON u.role_id = r.id
                 GROUP BY r.id ORDER BY r.level ASC'
            ),
        ], 'layouts/admin');
    }

    public function reports(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        if ($year < 2000 || $year > (int) date('Y') + 1) {
            $year = (int) date('Y');
        }

        $monthly = Stat::monthlySeries(12);
        $years = [];
        foreach (Stat::yearlySeries(6) as $row) {
            $years[] = $row;
        }

        $yearRows = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS k, COUNT(*) AS total
             FROM users WHERE YEAR(created_at) = ? GROUP BY k ORDER BY k ASC",
            [$year]
        );
        $registrationByMonth = [];
        foreach ($yearRows as $row) {
            $registrationByMonth[$row['k']] = (int) $row['total'];
        }

        $monthlyRows = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $year, $m);
            $monthlyRows[] = [
                'key' => $key,
                'label' => date('F', mktime(0, 0, 0, $m, 1, $year)),
                'users' => $registrationByMonth[$key] ?? 0,
                'blogs' => (int) Database::value(
                    "SELECT COUNT(*) FROM blogs WHERE status = 'published' AND DATE_FORMAT(published_at, '%Y-%m') = ?",
                    [$key],
                    0
                ),
                'likes' => (int) Database::value("SELECT COUNT(*) FROM likes WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$key], 0),
                'comments' => (int) Database::value("SELECT COUNT(*) FROM comments WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$key], 0),
                'views' => (int) Database::value("SELECT COUNT(*) FROM blog_views WHERE DATE_FORMAT(viewed_at, '%Y-%m') = ?", [$key], 0),
            ];
        }

        $this->view('admin/reports', [
            'pageTitle' => 'Reports — ' . setting('site_name', 'ObscuraX'),
            'metaDescription' => 'Monthly and yearly platform reports.',
            'bodyClass' => 'page-admin-reports',
            'year' => $year,
            'monthlyRows' => $monthlyRows,
            'monthly' => $monthly,
            'years' => $years,
            'yearsAvailable' => Database::fetchAll(
                "SELECT DISTINCT YEAR(created_at) AS y FROM users WHERE created_at IS NOT NULL ORDER BY y DESC"
            ),
        ], 'layouts/admin');
    }

    public function export(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        if ($year < 2000 || $year > (int) date('Y') + 1) {
            $year = (int) date('Y');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="obscurax-report-' . $year . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Month', 'New users', 'Published articles', 'Likes', 'Comments', 'Page views'], ',', '"', '');

        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $year, $m);
            fputcsv($out, [
                date('F Y', mktime(0, 0, 0, $m, 1, $year)),
                (int) Database::value("SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$key], 0),
                (int) Database::value("SELECT COUNT(*) FROM blogs WHERE status = 'published' AND DATE_FORMAT(published_at, '%Y-%m') = ?", [$key], 0),
                (int) Database::value("SELECT COUNT(*) FROM likes WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$key], 0),
                (int) Database::value("SELECT COUNT(*) FROM comments WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$key], 0),
                (int) Database::value("SELECT COUNT(*) FROM blog_views WHERE DATE_FORMAT(viewed_at, '%Y-%m') = ?", [$key], 0),
            ], ',', '"', '');
        }

        fclose($out);
        Activity::admin((int) Auth::id(), 'report.export', 'Exported the ' . $year . ' annual report');
        exit;
    }

    /** @return list<array> */
    private function categoryStats(): array
    {
        return Database::fetchAll(
            'SELECT c.name, c.slug, c.accent_color,
                    COUNT(b.id) AS blogs,
                    COALESCE(SUM(b.view_count), 0) AS views,
                    (SELECT COUNT(*) FROM likes l INNER JOIN blogs lb ON lb.id = l.blog_id WHERE lb.category_id = c.id) AS likes,
                    (SELECT COUNT(*) FROM comments cm INNER JOIN blogs cb ON cb.id = cm.blog_id WHERE cb.category_id = c.id) AS comments
             FROM categories c
             LEFT JOIN blogs b ON b.category_id = c.id AND b.status = "published"
             GROUP BY c.id
             ORDER BY views DESC'
        );
    }
}
