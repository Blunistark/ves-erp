<?php
require_once 'con.php';

echo "<h2>Teacher Assignment Routes Test</h2>";

echo "<h3>System Overview</h3>";
echo "<p>The teacher assignment system is now split into two distinct functionalities:</p>";

echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0;'>";

echo "<div style='border: 2px solid #3b82f6; padding: 1.5rem; border-radius: 8px;'>";
echo "<h4 style='color: #3b82f6; margin-top: 0;'>📚 Class Teacher Assignment</h4>";
echo "<p><strong>File:</strong> <code>teachersassign.php</code></p>";
echo "<p><strong>Purpose:</strong> Assign class teachers to specific classes/sections</p>";
echo "<p><strong>Fields:</strong></p>";
echo "<ul>";
echo "<li>Teacher (who will be the class teacher)</li>";
echo "<li>Class (which class)</li>";
echo "<li>Section (which section)</li>";
echo "</ul>";
echo "<p><strong>Result:</strong> Teacher becomes responsible for that class/section</p>";
echo "<a href='teachersassign.php' class='btn' style='background: #3b82f6; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 1rem;'>→ Assign Class Teachers</a>";
echo "</div>";

echo "<div style='border: 2px solid #10b981; padding: 1.5rem; border-radius: 8px;'>";
echo "<h4 style='color: #10b981; margin-top: 0;'>📖 Subject Teacher Assignment</h4>";
echo "<p><strong>File:</strong> <code>teachersassign_subject.php</code></p>";
echo "<p><strong>Purpose:</strong> Assign subjects to teachers (including class teachers)</p>";
echo "<p><strong>Fields:</strong></p>";
echo "<ul>";
echo "<li>Teacher (who will teach the subject)</li>";
echo "<li>Class (which class)</li>";
echo "<li>Section (which section)</li>";
echo "<li>Subject (which subject to teach)</li>";
echo "<li>Schedule (when to teach)</li>";
echo "</ul>";
echo "<p><strong>Result:</strong> Teacher can teach that subject to that class</p>";
echo "<a href='teachersassign_subject.php' class='btn' style='background: #10b981; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 1rem;'>→ Assign Subject Teachers</a>";
echo "</div>";

echo "</div>";

echo "<h3>Database Tables Used</h3>";
echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0;'>";

echo "<div style='background: #f8fafc; padding: 1rem; border-radius: 8px;'>";
echo "<h4>Class Teacher Assignments</h4>";
echo "<p><strong>Table:</strong> <code>sections</code></p>";
echo "<p><strong>Key Field:</strong> <code>class_teacher_user_id</code></p>";
echo "<p>Updates the section record to set the class teacher</p>";
echo "</div>";

echo "<div style='background: #f8fafc; padding: 1rem; border-radius: 8px;'>";
echo "<h4>Subject Teacher Assignments</h4>";
echo "<p><strong>Table:</strong> <code>teacher_assignments</code></p>";
echo "<p><strong>Fields:</strong> <code>teacher_user_id, class_id, section_id, subject_id</code></p>";
echo "<p>Creates records for each subject assignment</p>";
echo "</div>";

echo "</div>";

echo "<h3>Current Database Status</h3>";

// Check sections with class teachers
$sections_result = $conn->query("
    SELECT 
        c.name as class_name,
        s.name as section_name,
        u.full_name as class_teacher,
        COUNT(st.user_id) as student_count
    FROM sections s
    JOIN classes c ON s.class_id = c.id
    LEFT JOIN users u ON s.class_teacher_user_id = u.id
    LEFT JOIN students st ON s.id = st.section_id
    GROUP BY s.id, c.name, s.name, u.full_name
    ORDER BY c.name, s.name
");

echo "<h4>Class Teacher Assignments</h4>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
echo "<tr><th>Class</th><th>Section</th><th>Class Teacher</th><th>Students</th><th>Status</th></tr>";
while ($row = $sections_result->fetch_assoc()) {
    $status = $row['class_teacher'] ? 'Assigned' : 'Unassigned';
    $status_color = $row['class_teacher'] ? '#10b981' : '#ef4444';
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['section_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['class_teacher'] ?: 'Not assigned') . "</td>";
    echo "<td>" . $row['student_count'] . "</td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

// Check teacher assignments (subjects)
$assignments_result = $conn->query("
    SELECT 
        c.name as class_name,
        s.name as section_name,
        u.full_name as teacher_name,
        sub.name as subject_name,
        COUNT(*) as assignment_count
    FROM teacher_assignments ta
    JOIN classes c ON ta.class_id = c.id
    JOIN sections s ON ta.section_id = s.id
    JOIN users u ON ta.teacher_user_id = u.id
    JOIN subjects sub ON ta.subject_id = sub.id
    GROUP BY ta.teacher_user_id, ta.class_id, ta.section_id, ta.subject_id
    ORDER BY c.name, s.name, u.full_name
");

echo "<h4>Subject Teacher Assignments</h4>";
if ($assignments_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr><th>Class</th><th>Section</th><th>Teacher</th><th>Subject</th></tr>";
    while ($row = $assignments_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['section_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['teacher_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #64748b; font-style: italic;'>No subject assignments found yet.</p>";
}

echo "<h3>Quick Actions</h3>";
echo "<div style='display: flex; gap: 1rem; margin: 2rem 0;'>";
echo "<a href='teachersassign.php' style='background: #3b82f6; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px;'>Assign Class Teachers</a>";
echo "<a href='teachersassign_subject.php' style='background: #10b981; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px;'>Assign Subject Teachers</a>";
echo "<a href='teacher_subject_assign.php' style='background: #8b5cf6; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px;'>Manage Teacher Subjects</a>";
echo "<a href='teachersmanage.php' style='background: #64748b; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px;'>Manage Teachers</a>";
echo "</div>";

echo "<h3>System Flow</h3>";
echo "<ol>";
echo "<li><strong>Add Teachers:</strong> Use 'Manage Teachers' to add new teachers</li>";
echo "<li><strong>Assign Subjects to Teachers:</strong> Use 'Manage Teacher Subjects' to define which subjects each teacher can teach</li>";
echo "<li><strong>Assign Class Teachers:</strong> Use 'Assign Class Teachers' to make teachers responsible for specific classes</li>";
echo "<li><strong>Assign Subject Teachers:</strong> Use 'Assign Subject Teachers' to assign specific subjects to teachers for specific classes</li>";
echo "</ol>";

echo "<p style='margin-top: 2rem; padding: 1rem; background: #fef3c7; border-left: 4px solid #f59e0b;'>";
echo "<strong>Note:</strong> A teacher can be both a class teacher (responsible for a class) AND a subject teacher (teaching specific subjects to various classes).";
echo "</p>";
?> 