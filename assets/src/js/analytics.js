/**
 * Atlas Returns Analytics JavaScript
 *
 * @package AtlasReturns
 */

(function($) {
    'use strict';

    // Chart instances
    let trendChart = null;
    let reasonsChart = null;
    let costsChart = null;

    // DOM Elements
    const elements = {
        periodSelect: $('#atlr-period'),
        exportBtn: $('#atlr-export-csv'),
        refreshBtn: $('#atlr-refresh'),
        loading: $('#atlr-analytics-loading'),
        // Summary cards
        totalReturns: $('#atlr-total-returns'),
        returnRate: $('#atlr-return-rate'),
        totalRefunded: $('#atlr-total-refunded'),
        totalCharged: $('#atlr-total-charged'),
        // Tables
        topProductsTable: $('#atlr-top-products-table tbody'),
        recentReturnsTable: $('#atlr-recent-returns-table tbody'),
    };

    /**
     * Initialize analytics.
     */
    function init() {
        bindEvents();
        loadAnalyticsData();
    }

    /**
     * Bind event handlers.
     */
    function bindEvents() {
        elements.periodSelect.on('change', loadAnalyticsData);
        elements.refreshBtn.on('click', loadAnalyticsData);
        elements.exportBtn.on('click', exportCsv);
    }

    /**
     * Load all analytics data.
     */
    function loadAnalyticsData() {
        const period = elements.periodSelect.val();

        showLoading(true);

        $.ajax({
            url: atlrAnalytics.ajaxUrl,
            type: 'POST',
            data: {
                action: 'atlr_get_analytics_data',
                nonce: atlrAnalytics.nonce,
                period: period,
            },
            success: function(response) {
                if (response.success) {
                    updateSummaryCards(response.data.summary);
                    updateTrendChart(response.data.trend);
                    updateReasonsChart(response.data.by_reason);
                    updateCostsChart(response.data.summary);
                    updateTopProductsTable(response.data.top_products);
                    updateRecentReturnsTable(response.data.recent);
                } else {
                    console.error('Error loading analytics:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            },
            complete: function() {
                showLoading(false);
            },
        });
    }

    /**
     * Update summary cards.
     *
     * @param {Object} summary Summary data.
     */
    function updateSummaryCards(summary) {
        elements.totalReturns.text(summary.total_returns);
        elements.returnRate.text(summary.return_rate + '%');
        elements.totalRefunded.html(formatCurrency(summary.total_refunded));
        elements.totalCharged.html(formatCurrency(summary.total_charged));

        // Add color classes
        elements.totalRefunded.closest('.atlr-card').toggleClass('atlr-negative', summary.total_refunded > 0);
        elements.totalCharged.closest('.atlr-card').toggleClass('atlr-positive', summary.total_charged > 0);
    }

    /**
     * Update trend chart.
     *
     * @param {Object} trendData Trend data.
     */
    function updateTrendChart(trendData) {
        const ctx = document.getElementById('atlr-trend-chart');

        if (!ctx) return;

        const labels = trendData.data.map(item => item.label);
        const counts = trendData.data.map(item => item.count);
        const refunded = trendData.data.map(item => item.refunded);
        const charged = trendData.data.map(item => item.charged);

        if (trendChart) {
            trendChart.destroy();
        }

        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: atlrAnalytics.i18n.returns,
                        data: counts,
                        borderColor: atlrAnalytics.colors.primary,
                        backgroundColor: hexToRgba(atlrAnalytics.colors.primary, 0.1),
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                    },
                    {
                        label: atlrAnalytics.i18n.refunded,
                        data: refunded,
                        borderColor: atlrAnalytics.colors.danger,
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1',
                    },
                    {
                        label: atlrAnalytics.i18n.charged,
                        data: charged,
                        borderColor: atlrAnalytics.colors.success,
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: atlrAnalytics.i18n.returns,
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: atlrAnalytics.i18n.costDifference,
                        },
                    },
                },
            },
        });
    }

    /**
     * Update reasons chart.
     *
     * @param {Array} reasonsData Reasons data.
     */
    function updateReasonsChart(reasonsData) {
        const ctx = document.getElementById('atlr-reasons-chart');

        if (!ctx) return;

        const labels = reasonsData.map(item => item.label);
        const counts = reasonsData.map(item => item.count);

        const colors = [
            atlrAnalytics.colors.primary,
            atlrAnalytics.colors.secondary,
            atlrAnalytics.colors.warning,
            atlrAnalytics.colors.info,
        ];

        if (reasonsChart) {
            reasonsChart.destroy();
        }

        if (reasonsData.length === 0) {
            ctx.parentElement.innerHTML = '<p class="atlr-no-data">' + atlrAnalytics.i18n.noData + '</p>';
            return;
        }

        reasonsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }

    /**
     * Update costs chart.
     *
     * @param {Object} summary Summary data with cost breakdown.
     */
    function updateCostsChart(summary) {
        const ctx = document.getElementById('atlr-costs-chart');

        if (!ctx) return;

        if (costsChart) {
            costsChart.destroy();
        }

        costsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [atlrAnalytics.i18n.refunded, atlrAnalytics.i18n.charged],
                datasets: [{
                    data: [summary.total_refunded, summary.total_charged],
                    backgroundColor: [
                        atlrAnalytics.colors.danger,
                        atlrAnalytics.colors.success,
                    ],
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    /**
     * Update top products table.
     *
     * @param {Array} products Top products data.
     */
    function updateTopProductsTable(products) {
        let html = '';

        if (products.length === 0) {
            html = '<tr><td colspan="3" class="atlr-no-data">' + atlrAnalytics.i18n.noData + '</td></tr>';
        } else {
            products.forEach(function(product) {
                html += '<tr>';
                html += '<td>' + escapeHtml(product.name) + '</td>';
                html += '<td class="atlr-col-sku"><code>' + escapeHtml(product.sku) + '</code></td>';
                html += '<td class="atlr-col-count"><span class="atlr-count-badge">' + product.return_count + '</span></td>';
                html += '</tr>';
            });
        }

        elements.topProductsTable.html(html);
    }

    /**
     * Update recent returns table.
     *
     * @param {Array} returns Recent returns data.
     */
    function updateRecentReturnsTable(returns) {
        let html = '';

        if (returns.length === 0) {
            html = '<tr><td colspan="6" class="atlr-no-data">' + atlrAnalytics.i18n.noData + '</td></tr>';
        } else {
            returns.forEach(function(ret) {
                const costClass = ret.cost_difference < 0 ? 'atlr-negative' : (ret.cost_difference > 0 ? 'atlr-positive' : '');

                html += '<tr>';
                html += '<td class="atlr-col-id">' + ret.id + '</td>';
                html += '<td><a href="' + ret.original_url + '">#' + ret.original_order_id + '</a></td>';
                html += '<td>';
                if (ret.return_order_id) {
                    html += '<a href="' + ret.return_url + '">#' + ret.return_order_id + '</a>';
                } else {
                    html += '&mdash;';
                }
                html += '</td>';
                html += '<td>' + escapeHtml(ret.reason_label) + '</td>';
                html += '<td class="atlr-col-cost ' + costClass + '">' + formatCurrency(ret.cost_difference) + '</td>';
                html += '<td>' + escapeHtml(ret.formatted_date) + '</td>';
                html += '</tr>';
            });
        }

        elements.recentReturnsTable.html(html);
    }

    /**
     * Export CSV.
     */
    function exportCsv() {
        const period = elements.periodSelect.val();

        showLoading(true);

        $.ajax({
            url: atlrAnalytics.ajaxUrl,
            type: 'POST',
            data: {
                action: 'atlr_export_csv',
                nonce: atlrAnalytics.nonce,
                period: period,
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    window.location.href = response.data.url;
                } else {
                    alert('Export failed.');
                }
            },
            error: function() {
                alert('Export failed.');
            },
            complete: function() {
                showLoading(false);
            },
        });
    }

    /**
     * Show/hide loading overlay.
     *
     * @param {boolean} show Whether to show loading.
     */
    function showLoading(show) {
        if (show) {
            elements.loading.show();
        } else {
            elements.loading.hide();
        }
    }

    /**
     * Format currency.
     *
     * @param {number} amount Amount to format.
     * @return {string} Formatted currency.
     */
    function formatCurrency(amount) {
        // Simple formatting - in production, use WooCommerce settings
        const formatted = parseFloat(amount).toFixed(2);
        return '€' + formatted;
    }

    /**
     * Convert hex color to rgba.
     *
     * @param {string} hex Hex color.
     * @param {number} alpha Alpha value.
     * @return {string} RGBA string.
     */
    function hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    /**
     * Escape HTML.
     *
     * @param {string} text Text to escape.
     * @return {string} Escaped text.
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
