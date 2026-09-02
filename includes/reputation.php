<?php

/*
|--------------------------------------------------------------------------
| Reputation Scoring
|--------------------------------------------------------------------------
|
| users.reputation_points was referenced across the codebase (profile
| pages, lecturer student view) but nothing ever calculated or wrote to
| it. This file completes that feature: a transparent, explainable score
| built from things a lecturer or peer has actually vouched for —
| verified skills, approved resources, collaboration reviews, and
| completed collaborations — rather than raw activity counts, which are
| easy to inflate and don't reflect actual trustworthiness.
|
| Scoring (kept simple and explainable for a viva/defense):
|   +10  per lecturer-verified skill
|   +5   per lecturer-approved resource
|   +2   per completed (accepted) collaboration
|   + (average peer review rating out of 5) x 4, rounded
|
| recalculate_reputation() is called at every point that changes one of
| these inputs: skill verification, resource approval, collaboration
| acceptance, and review submission.
|
*/

function recalculate_reputation($conn, $user_id) {

    $user_id = (int) $user_id;

    $verified_skills = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM skills WHERE user_id = $user_id AND verified = 1"
    ))['total'];

    $approved_resources = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM resources WHERE user_id = $user_id AND status = 'Approved'"
    ))['total'];

    $completed_collaborations = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM collaboration_requests
         WHERE (sender_id = $user_id OR receiver_id = $user_id)
         AND status = 'Accepted'"
    ))['total'];

    $avg_rating_row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT AVG(rating) AS avg_rating FROM reviews WHERE reviewed_user_id = $user_id"
    ));
    $avg_rating = $avg_rating_row['avg_rating'] !== null ? (float) $avg_rating_row['avg_rating'] : 0;

    $score = ($verified_skills * 10)
           + ($approved_resources * 5)
           + ($completed_collaborations * 2)
           + (int) round($avg_rating * 4);

    $stmt = mysqli_prepare($conn, "UPDATE users SET reputation_points = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $score, $user_id);
    mysqli_stmt_execute($stmt);

    return $score;
}
