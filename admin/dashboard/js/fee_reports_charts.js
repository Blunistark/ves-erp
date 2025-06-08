/**
 * Fee Reports Charts
 * Visualizations for fee collection statistics
 */

/**
 * Apply white backgrounds to all chart elements
 */
function applyWhiteBackgrounds() {
    // Apply white background to all chart cards
    document.querySelectorAll('.chart-card').forEach(function(card) {
        card.style.backgroundColor = '#ffffff';
        card.style.color = '#333333';
    });
    
    document.querySelectorAll('.chart-header').forEach(function(header) {
        header.style.backgroundColor = '#ffffff';
        header.style.color = '#333333';
    });
    
    document.querySelectorAll('.chart-title').forEach(function(title) {
        title.style.color = '#333333';
    });
    
    document.querySelectorAll('.chart-body').forEach(function(body) {
        body.style.backgroundColor = '#ffffff';
    });
}

/**
 * Ensure the specified chart container has a canvas with the correct ID
 */
function ensureCanvas(chartId) {
    // Find the chart container for this specific chart type
    // First try to find a container with a canvas having this ID
    // Then try to find a container that had this ID before
    // Then try to match by position
    
    let container = null;
    
    // Try to find by existing canvas ID
    const existingCanvas = document.querySelector(`canvas#${chartId}`);
    if (existingCanvas) {
        container = existingCanvas.closest('.chart-body');
    }
    
    // If not found, try to find the container by index based on chart type
    if (!container) {
        const allContainers = document.querySelectorAll('.chart-body');
        if (chartId === 'collectionProgressChart' && allContainers.length > 0) {
            container = allContainers[0];
        } else if (chartId === 'monthlyCollectionChart' && allContainers.length > 1) {
            container = allContainers[1];
        } else if (chartId === 'classwiseComparisonChart' && allContainers.length > 2) {
            container = allContainers[2];
        } else if (chartId === 'paymentMethodsChart' && allContainers.length > 3) {
            container = allContainers[3];
        }
    }
    
    if (!container) return;
    
    // Clear any no-data messages
    const noDataMsg = container.querySelector('.no-data-message');
    if (noDataMsg) {
        container.removeChild(noDataMsg);
    }
    
    // Ensure canvas exists with the right ID
    let canvas = container.querySelector(`canvas#${chartId}`);
    if (!canvas) {
        // Clear the container first to avoid duplication
        container.innerHTML = '';
        
        // Create new canvas with the right ID
        canvas = document.createElement('canvas');
        canvas.id = chartId;
        container.appendChild(canvas);
    }
}

/**
 * Clear "no data" messages from all chart containers
 */
function clearNoDataMessages() {
    // Make sure each chart container has the right canvas with the correct ID
    ensureCanvas('collectionProgressChart');
    ensureCanvas('monthlyCollectionChart');
    ensureCanvas('classwiseComparisonChart');
    ensureCanvas('paymentMethodsChart');
    }
    
document.addEventListener('DOMContentLoaded', function() {
    // Listen for the feeSummaryLoaded event for when actual data loads
    document.addEventListener('feeSummaryLoaded', function(e) {
        try {
            // Get data from the event
            const data = e.detail;
            

            
            // Convert all values to numbers to ensure they're valid
        const totalFees = parseFloat(data.total_fees) || 0;
        const totalCollected = parseFloat(data.total_collected) || 0;
        const pendingFees = parseFloat(data.pending_fees) || 0;
            const overdueFees = parseFloat(data.overdue_fees) || 0;
            
            // First clear any "no data" messages from all chart containers
            clearNoDataMessages();
            
            // Initialize all charts with the data
            initCollectionProgressChart(totalFees, totalCollected, pendingFees);
        
            // Initialize other charts if the data is available
            if (data.monthly_data && data.monthly_data.length > 0) {
                initMonthlyCollectionChart(data.monthly_data);
            }
            
            if (data.classwise_data && data.classwise_data.length > 0) {
                initClasswiseComparisonChart(data.classwise_data);
            }
            
            if (data.payment_methods && data.payment_methods.length > 0) {
                initPaymentMethodsChart(data.payment_methods);
            }
            
            // Make sure all chart backgrounds are white
            applyWhiteBackgrounds();
            

        } catch (error) {
            console.error('Error updating charts with API data:', error);
            // Initialize charts with default values if data isn't available
            initChartsWithDefaultValues();
        }
    });
    
    // Initialize charts with default values initially
    initChartsWithDefaultValues();
    
    /**
     * Initialize charts with default values
     */
    function initChartsWithDefaultValues() {
        // Get all chart elements
        const chartContainers = document.querySelectorAll('.chart-body');
        
        // For each chart container, show a "No data available" message
        chartContainers.forEach(container => {
            // Clear any existing canvas to avoid stacking content
            container.innerHTML = '';
            
            // Add no data message
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'no-data-message';
            noDataDiv.innerHTML = `
                <p>No data available. Please adjust filters or add fee records.</p>
            `;
            container.appendChild(noDataDiv);
        });
        
        // Also add some CSS for the no-data message
        const style = document.createElement('style');
        style.textContent = `
            .no-data-message {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100%;
                color: #999;
                font-style: italic;
                text-align: center;
            }
        `;
        document.head.appendChild(style);
    }
});
    
    /**
 * Initialize the collection progress chart
     */
function initCollectionProgressChart(totalFees, totalCollected, pendingFees) {
    try {
        // Check if Chart class is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please include Chart.js before using this script.');
            return;
        }
        
        // Ensure canvas is ready
        ensureCanvas('collectionProgressChart');
        
        const ctx = document.getElementById('collectionProgressChart');
        if (!ctx) {
            console.error('Collection progress chart canvas not found');
            return;
        }
        
        // If chart already exists, destroy it
        if (window.collectionChart) {
            window.collectionChart.destroy();
        }
        
        // Calculate the percentage values
        const collectedPercentage = totalFees > 0 ? (totalCollected / totalFees) * 100 : 0;
        const pendingPercentage = 100 - collectedPercentage;
        
        // Ensure we're working with valid numbers
        const safeCollectedPercentage = isNaN(collectedPercentage) ? 0 : Math.min(100, collectedPercentage);
        const safePendingPercentage = isNaN(pendingPercentage) ? 100 : Math.max(0, pendingPercentage);
        
        // Create chart
        window.collectionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Collected', 'Pending'],
                datasets: [{
                    data: [safeCollectedPercentage, safePendingPercentage],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                return `${label}: ${value.toFixed(1)}%`;
                            }
                        }
                    }
                },
                // Add white background
                backgroundColor: 'white'
            }
        });
        
        // Set white background to chart container
        ctx.parentElement.style.backgroundColor = 'white';
        
        // Add text below chart showing actual values
        const chartContainer = ctx.parentElement;
        const statsElement = document.createElement('div');
        statsElement.className = 'chart-stats';
        statsElement.innerHTML = `
            <div class="stat-item collected">
                <span class="stat-label">Collected:</span>
                <span class="stat-value">₹${formatAmount(totalCollected)}</span>
            </div>
            <div class="stat-item pending">
                <span class="stat-label">Pending:</span>
                <span class="stat-value">₹${formatAmount(pendingFees)}</span>
                </div>
            <div class="stat-item total">
                <span class="stat-label">Total:</span>
                <span class="stat-value">₹${formatAmount(totalFees)}</span>
            </div>
        `;
        
        // Remove existing stats if any
        const existingStats = chartContainer.querySelector('.chart-stats');
        if (existingStats) {
            chartContainer.removeChild(existingStats);
        }
        
        // Append new stats
        chartContainer.appendChild(statsElement);
        

    } catch (error) {
        console.error('Error creating collection progress chart:', error);
    }
    }
    
    /**
 * Initialize the monthly collection chart
     */
function initMonthlyCollectionChart(monthlyData) {
    try {
        // Check if Chart class is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please include Chart.js before using this script.');
            return;
        }
        
        // Ensure canvas is ready
        ensureCanvas('monthlyCollectionChart');
        
        const ctx = document.getElementById('monthlyCollectionChart');
        if (!ctx) {
            console.error('Monthly collection chart canvas not found');
            return;
        }
        
        // Set white background to chart container
        ctx.parentElement.style.backgroundColor = 'white';
        
        // If chart already exists, destroy it
        if (window.monthlyChart) {
            window.monthlyChart.destroy();
        }
        
        // Find max value for scaling
        const maxValue = Math.max(
            ...monthlyData.map(item => parseInt(item.collected) || 0),
            ...monthlyData.map(item => parseInt(item.pending) || 0)
        );
        
        // Add 20% padding to max value for better visualization
        const yAxisMax = Math.ceil(maxValue * 1.2);
        
        // Prepare data for the chart
        const months = monthlyData.map(item => item.month);
        const collectedValues = monthlyData.map(item => item.collected);
        const pendingValues = monthlyData.map(item => item.pending || 0);
        
        // Create chart
        window.monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Collected',
                        data: collectedValues,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1,
                        maxBarThickness: 50 // Limit bar width
                    },
                    {
                        label: 'Pending',
                        data: pendingValues,
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1,
                        maxBarThickness: 50 // Limit bar width
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                backgroundColor: 'white',
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#333'
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        max: yAxisMax, // Set max value with padding
                        ticks: {
                            color: '#333',
                            callback: function(value) {
                                return '₹' + formatAmount(value);
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.parsed.y || 0;
                                return `${label}: ₹${formatAmount(value)}`;
                            }
                        }
                    }
                }
            }
        });
        

    } catch (error) {
        console.error('Error creating monthly collection chart:', error);
    }
}

/**
 * Initialize the classwise comparison chart
 */
function initClasswiseComparisonChart(classwiseData) {
    try {
        // Check if Chart class is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please include Chart.js before using this script.');
            return;
        }
        
        // Ensure canvas is ready
        ensureCanvas('classwiseComparisonChart');
        
        const ctx = document.getElementById('classwiseComparisonChart');
        if (!ctx) {
            console.error('Classwise comparison chart canvas not found');
            return;
        }
        
        // Set white background to chart container
        ctx.parentElement.style.backgroundColor = 'white';
        
        // If chart already exists, destroy it
        if (window.classwiseChart) {
            window.classwiseChart.destroy();
        }
        
        // Prepare data for the chart
        const classes = classwiseData.map(item => item.class);
        const percentages = classwiseData.map(item => item.percentage);
        
        // Create chart
        window.classwiseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: classes,
                datasets: [{
                    label: 'Collection Percentage',
                    data: percentages,
                    backgroundColor: '#4a6cf7',
                    borderColor: '#4a6cf7',
                    borderWidth: 1,
                    maxBarThickness: 50 // Limit bar width
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                backgroundColor: 'white',
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#333'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#333',
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.parsed.y || 0;
                                return `${label}: ${value}%`;
                            }
                        }
                    }
                }
            }
        });
        

    } catch (error) {
        console.error('Error creating classwise comparison chart:', error);
    }
    }
    
    /**
 * Initialize the payment methods chart
     */
function initPaymentMethodsChart(methodsData) {
    try {
        // Check if Chart class is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Please include Chart.js before using this script.');
            return;
        }
        
        // Ensure canvas is ready
        ensureCanvas('paymentMethodsChart');
        
        const ctx = document.getElementById('paymentMethodsChart');
        if (!ctx) {
            console.error('Payment methods chart canvas not found');
            return;
        }
        
        // Set white background to chart container
        ctx.parentElement.style.backgroundColor = 'white';
        
        // If chart already exists, destroy it
        if (window.methodsChart) {
            window.methodsChart.destroy();
        }
        
        // Normalize payment methods - combine similar methods like "Bank transfer" and "Bank_transfer"
        let normalizedData = [];
        let methodMap = {};
        
        // Group by normalized method name
        methodsData.forEach(item => {
            // Normalize method name (lowercase, remove underscores, trim)
            const normalizedName = item.method.toLowerCase().replace(/_/g, ' ').trim();
            
            if (!methodMap[normalizedName]) {
                methodMap[normalizedName] = {
                    method: item.method, // Keep original for display
                    amount: 0,
                    count: 0,
                    color: item.color
                };
            }
            
            methodMap[normalizedName].amount += parseFloat(item.amount);
            methodMap[normalizedName].count += parseInt(item.count || 1);
        });
            
        // Convert back to array
        for (const key in methodMap) {
            normalizedData.push(methodMap[key]);
        }
        
        // Prepare data for the chart
        const methods = normalizedData.map(item => item.method);
        const amounts = normalizedData.map(item => item.amount);
        const colors = normalizedData.map(item => item.color);
        

        
        // Create chart
        window.methodsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: methods,
                datasets: [{
                    data: amounts,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                backgroundColor: 'white',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                return `${label}: ₹${formatAmount(value)}`;
                            }
                        }
                    }
                }
            }
        });
        

    } catch (error) {
        console.error('Error creating payment methods chart:', error);
    }
    }
    
    /**
 * Format amount with thousands separator
     */
    function formatAmount(amount) {
        return new Intl.NumberFormat('en-IN', {
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    /**
     * Chart filters click handlers
     */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.chart-filter').forEach(filter => {
        filter.addEventListener('click', function() {
            const chartCard = this.closest('.chart-card');
            const chartId = chartCard.querySelector('.chart-body').id;
            const period = this.dataset.period;
            
            // Remove active class from all filters in this chart
            chartCard.querySelectorAll('.chart-filter').forEach(f => {
                f.classList.remove('active');
            });
            
            // Add active class to clicked filter
            this.classList.add('active');
            
            // Update chart data based on period and chart type
            if (chartId === 'collectionProgressChart') {
                // Handle collection progress filter click

                // In a real app, you would fetch data for the selected period
            } else if (chartId === 'monthlyCollectionChart') {
                // Handle monthly collection filter click

            } else if (chartId === 'classwiseComparisonChart') {
                // Handle classwise comparison filter click

            } else if (chartId === 'paymentMethodsChart') {
                // Handle payment methods filter click

            }
        });
    });
}); 