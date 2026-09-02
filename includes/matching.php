<?php

/*
|--------------------------------------------------------------------------
| Skill-Based Collaboration Matching Engine
|--------------------------------------------------------------------------
|
| This is what distinguishes SkillLink UNIMTECH from a generic directory,
| chat group, or classroom tool: instead of only letting a student search
| for peers manually, the platform actively recommends who to collaborate
| with, and explains WHY each recommendation was made.
|
| The score is intentionally simple and fully explainable (important for
| a viva: every point awarded can be traced back to a concrete reason),
| rather than a black-box similarity metric.
|
| For a candidate student C, scored against the current student S:
|
|   +5  for each skill S and C share, where C's level is HIGHER than
|       S's (C can mentor S — a genuine learning opportunity)
|   +3  for each skill S and C share at the SAME level
|       (peer-level collaboration)
|   +3  bonus, per skill above, if C's skill is lecturer-verified
|       (verified expertise is worth more than a self-reported claim)
|   +2  if S and C are in the same department (practical, easier
|       to actually meet and work together)
|   +1  per point of C's own reputation score, capped contribution
|       at 10, so an established contributor is surfaced more often
|       without one "reputation whale" dominating every result
|
| Students already connected (an Accepted collaboration already exists)
| are excluded, since the goal is to recommend NEW collaborators.
|
*/

function get_recommended_collaborators($conn, $current_user_id, $limit = 5) {

    $current_user_id = (int) $current_user_id;

    // ---- Current student's own skills & department ----

    $own_skills = [];
    $skills_result = mysqli_query(
        $conn,
        "SELECT skill_name, skill_level FROM skills WHERE user_id = $current_user_id"
    );
    while ($row = mysqli_fetch_assoc($skills_result)) {
        $own_skills[strtolower(trim($row['skill_name']))] = $row['skill_level'];
    }

    $own_dept_row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT department FROM users WHERE id = $current_user_id"
    ));
    $own_department = $own_dept_row['department'] ?? null;

    $level_rank = ['Beginner' => 1, 'Intermediate' => 2, 'Advanced' => 3];

    // ---- Already-connected students, to exclude ----

    $excluded_ids = [$current_user_id];
    $connected_result = mysqli_query(
        $conn,
        "SELECT sender_id, receiver_id FROM collaboration_requests
         WHERE (sender_id = $current_user_id OR receiver_id = $current_user_id)
         AND status = 'Accepted'"
    );
    while ($row = mysqli_fetch_assoc($connected_result)) {
        $excluded_ids[] = (int) $row['sender_id'];
        $excluded_ids[] = (int) $row['receiver_id'];
    }
    $excluded_ids = array_unique($excluded_ids);
    $excluded_list = implode(',', $excluded_ids);

    // ---- Candidate pool: every other student, with their skills ----

    $candidates = [];
    $candidates_result = mysqli_query(
        $conn,
        "SELECT id, full_name, department, programme, level,
                profile_picture, reputation_points
         FROM users
         WHERE role = 'student'
         AND id NOT IN ($excluded_list)
         AND status = 'Active'"
    );

    while ($candidate = mysqli_fetch_assoc($candidates_result)) {

        $candidate_id = (int) $candidate['id'];
        $score = 0;
        $reasons = [];

        $cand_skills_result = mysqli_query(
            $conn,
            "SELECT skill_name, skill_level, verified
             FROM skills WHERE user_id = $candidate_id"
        );

        while ($cand_skill = mysqli_fetch_assoc($cand_skills_result)) {

            $name = trim($cand_skill['skill_name']);
            $key = strtolower($name);

            if (!isset($own_skills[$key])) {
                continue;
            }

            $own_rank = $level_rank[$own_skills[$key]] ?? 1;
            $cand_rank = $level_rank[$cand_skill['skill_level']] ?? 1;

            if ($cand_rank > $own_rank) {

                $score += 5;
                $verified_note = $cand_skill['verified'] ? ', lecturer-verified' : '';
                $reasons[] = "Can mentor you in \"$name\" ({$cand_skill['skill_level']}$verified_note)";

                if ($cand_skill['verified']) {
                    $score += 3;
                }

            } elseif ($cand_rank === $own_rank) {

                $score += 3;
                $reasons[] = "Shares your skill level in \"$name\"";

                if ($cand_skill['verified']) {
                    $score += 3;
                }
            }
        }

        if ($own_department && $candidate['department'] === $own_department) {
            $score += 2;
            $reasons[] = "Also in " . $candidate['department'];
        }

        $score += (int) min(10, (int) $candidate['reputation_points']);

        if ($score > 0) {
            $candidates[] = [
                'id' => $candidate_id,
                'full_name' => $candidate['full_name'],
                'department' => $candidate['department'],
                'programme' => $candidate['programme'],
                'level' => $candidate['level'],
                'profile_picture' => $candidate['profile_picture'],
                'reputation_points' => (int) $candidate['reputation_points'],
                'score' => $score,
                'reasons' => $reasons,
            ];
        }
    }

    usort($candidates, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return array_slice($candidates, 0, $limit);
}
