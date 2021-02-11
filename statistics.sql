-- ===========================================
-- ====== What queries will I need ? =========
-- ===========================================


-- ===========================================
-- ====== TICKET STATUS PIE CHART ============
-- ===========================================

-- Amount of closed tickets
SELECT COUNT(t.id) AS Closed_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name
FROM ticket t, problem p, problem_detail pd
WHERE ticketstatus = 2 AND t.problem = p.id AND t.problemdetail = pd.id AND p.id = ? AND pd.id = ? ;

-- Amount of open tickets
SELECT COUNT(t.id) AS Open_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name
FROM ticket t, problem p, problem_detail pd
WHERE ticketstatus = 1 AND t.problem = p.id AND t.problemdetail = pd.id AND p.id = ? AND pd.id = ? ;


-- ===========================================
-- ====== TICKETS OPENED OVER PERIOD =========
-- ===========================================

-- Tickets closed over specific period
SELECT count(id) AS CLOSED_TICKETS_OVER_PERIOD
FROM ticket
WHERE DATE(created) BETWEEN ? AND NOW() AND ticketstatus = 2;
-- "2021-01-01"

-- Tickets opened over specific period
SELECT count(id) AS OPEN_TICKETS_OVER_PERIOD
FROM ticket
WHERE DATE(created) BETWEEN ? AND NOW();
-- "2021-01-01"

-- + % of all tickets closed


-- ===========================================
-- =========== PROBLEM GRAPH =================
-- ===========================================

SELECT COUNT(t.id) AS Open_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name, ci.acct_name AS Company_Name
FROM ticket t, problem p, problem_detail pd, cust_info ci
WHERE ticketstatus = ? AND t.problem = p.id AND t.problemdetail = pd.id AND ci.id = t.custinfo AND p.id = ? AND pd.id = ? AND ci.id = ?;

-- ticketstatus selectable OPEN / CLOSED / ALL
-- p.id selectable, which will return problem types + select all options
-- pd.id selectable, which will rturn problem details depending on type + select all options
-- ci.id selectable, which will return all company names + select all options


-- ===========================================
-- ========= DAILY TICKET TRACKER ============
-- ===========================================

-- Daily Closed Tickets
SELECT COUNT(t.id) AS Closed_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name
FROM ticket t, problem p, problem_detail pd
WHERE DATE(t.created) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) AND DATE(NOW()) ticketstatus = 2  AND t.problem = p.id AND t.problemdetail = pd.id AND p.id = ? AND pd.id = ? ;

-- Daily Opened Tickets
SELECT COUNT(t.id) AS Open_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name
FROM ticket t, problem p, problem_detail pd
WHERE DATE(t.created) BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) AND DATE(NOW()) ticketstatus = 1  AND t.problem = p.id AND t.problemdetail = pd.id AND p.id = ? AND pd.id = ? ;


-- ===========================================
-- ========= EMPLOYEE PERFORMANCE ============
-- ===========================================

-- How many tickets has employee X opened ?
SELECT count(th.id) AS Total_Tickets_Opened, u.name AS Employee_Name, u.employe AS Is_Employee
FROM ticket_history th, users u
WHERE th.id IN ( SELECT min(id) FROM ticket_history GROUP BY ticket ) AND th.user = ? and th.ticketstatus = 1 AND u.id = th.user AND u.employe = 1;

-- How many tickets has employee X closed ?
SELECT COUNT(th.id) as Total_Tickets_Closed, th.ticket, th.user, th.updated, u.name, u.employe
FROM ticket_history th, users u
WHERE th.user = u.id AND th.id IN ( SELECT max(th.id) FROM ticket_history GROUP BY ticket ) AND user = ? and ticketstatus = 2 and u.employe = 1;

-- AND DATE(t.created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
-- PERIOD CONTROL ?


-- ===========================================
-- =========== COMPANY REQUESTS ==============
-- ===========================================

-- How many tickets have been opened over the past week by company X ?
SELECT COUNT(t.id) AS Open_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name, ci.acct_name AS Company_Name, t.created AS Ticket_Date_Created
FROM ticket t, problem p, problem_detail pd, cust_info ci
WHERE t.problem = p.id AND t.problemdetail = pd.id AND ci.id = t.custinfo AND ci.id = 1 AND DATE(t.created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE();

-- How many tickets have been opened over the past month by company X ?
SELECT COUNT(t.id) AS Open_Tickets, p.name AS Problem_Name, pd.name AS ProblemDetail_Name, ci.acct_name AS Company_Name, t.created AS Ticket_Date_Created
FROM ticket t, problem p, problem_detail pd, cust_info ci
WHERE t.problem = p.id AND t.problemdetail = pd.id AND ci.id = t.custinfo AND ci.id = 1 AND DATE(t.created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE();

-- AND p.id = ? AND pd.id = ?
-- OPTIONAL PROBLEM FILTERING


-- ===========================================
-- ============ 24 HOUR GRID =================
-- ===========================================

-- Hourly open ticket tracker over the past week
SELECT th.ticket,u.name AS Employee_Name, u.employe AS Is_Employee, th.updated as Updated_At, HOUR(th.updated) as Hour_Opened, COUNT(HOUR(th.updated)) AS Amount_Created_At_Hour
FROM ticket_history th, users u
WHERE th.id IN ( SELECT min(id) FROM ticket_history GROUP BY ticket ) and th.ticketstatus = 1 AND u.id = th.user AND u.employe = 1 AND DATE(th.updated) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE() GROUP BY HOUR(th.updated) ORDER BY Hour_Opened;


-- Hourly closed ticket tracker over the past week
SELECT th.ticket,u.name AS Employee_Name, u.employe AS Is_Employee, th.updated as Updated_At, HOUR(th.updated) as Hour_Closed, COUNT(HOUR(th.updated)) AS Amount_Closed_At_Hour
FROM ticket_history th, users u
WHERE th.id IN ( SELECT max(th.id) FROM ticket_history GROUP BY ticket ) and th.ticketstatus = 2 AND u.id = th.user AND u.employe = 1 AND DATE(th.updated) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE() GROUP BY HOUR(th.updated) ORDER BY Hour_Closed;

-- AND u.id = ?
-- User filter

-- WEEKLY & MONTHLY !!!

-- PDF EXPORT