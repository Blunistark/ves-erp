// Debug tab switching functionality
console.log("=== TAB SWITCHING DEBUG ===");

// Check if jQuery is loaded
console.log("jQuery loaded:", typeof $ !== 'undefined');

// Check tab buttons
console.log("Tab buttons found:", $('.tab-button').length);
$('.tab-button').each(function(index) {
    console.log(`Button ${index}: data-tab="${$(this).data('tab')}", text="${$(this).text().trim()}"`);
});

// Check tab contents
console.log("Tab contents found:", $('.tab-content').length);
$('.tab-content').each(function(index) {
    console.log(`Content ${index}: id="${this.id}", visible="${$(this).is(':visible')}", hasActive="${$(this).hasClass('active')}"`);
});

// Check if switchTab function exists
console.log("switchTab function exists:", typeof switchTab !== 'undefined');

// Test switching to manage-teachers tab
console.log("Testing switchTab('manage-teachers')...");
if (typeof switchTab !== 'undefined') {
    switchTab('manage-teachers');
    setTimeout(() => {
        console.log("After switchTab - manage-teachers visible:", $('#manage-teachers').is(':visible'));
        console.log("After switchTab - manage-teachers has active class:", $('#manage-teachers').hasClass('active'));
    }, 100);
} else {
    console.error("switchTab function not found!");
}

// Check if click handlers are attached
console.log("Testing click handler...");
$('.tab-button[data-tab="class-assignments"]').click();
setTimeout(() => {
    console.log("After click - class-assignments visible:", $('#class-assignments').is(':visible'));
    console.log("After click - class-assignments has active class:", $('#class-assignments').hasClass('active'));
}, 100);
