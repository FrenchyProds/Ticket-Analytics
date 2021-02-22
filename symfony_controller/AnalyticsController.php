<?php

namespace App\Controller;

use DateTime;
use App\Entity\Level;
use App\Entity\Users;
use App\Entity\Ticket;
use DateTimeImmutable;
//use Doctrine\DBAL\Connection;
use App\Entity\Problem;
use App\Entity\CustInfo;
use App\Entity\TypeUser;
use App\Entity\Privilege;
use App\Entity\TicketStatus;
use App\Entity\TicketUpload;
use App\Entity\ProblemDetail;
use App\Entity\TicketHistory;
use Doctrine\ORM\EntityManager;
use App\Entity\EscalationMatrix;
use Doctrine\ORM\Query\Expr\Math;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Translation\TranslatorInterface;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CMEN\GoogleChartsBundle\GoogleCharts\Options\PieChart\PieSlice;

class AnalyticsController extends Controller {

    // ========================================================
    // ========================================================
    // ==================== RAW DATA ==========================
    // ========================================================
    // ========================================================

    // ticket id, ticket age, comment, status

    #[Route('/dashboard/rawdata/{startDate}/{endDate}')]
    public function rawData(Request $request, $startDate, $endDate) {
        $req = $this->getDoctrine()->getManager();

        $fetchData =
            'SELECT p.name AS problemName, pd.name AS detailName, ci.acct_name AS companyName, u.name AS createdBy, t.created AS dateCreated, u.employe AS employeeStatus, t.id AS ticketId, ts.name AS ticketStatus, t.level AS ticketLevel, t.subject AS ticketSubject, (SELECT uu.name FROM ticket_history tth, users uu WHERE tth.user = uu.id AND tth.ticketstatus = 2 AND th.ticket = tth.ticket GROUP BY tth.ticket) AS closedBy, (SELECT max(updated) FROM ticket_history WHERE ticketstatus = 2 AND th.ticket = ticket GROUP BY ticket) as dateClosed 
                FROM problem p, problem_detail pd, cust_info ci, users u, ticket t, ticket_status ts, level l, ticket_history th
                    WHERE t.problem = p.id AND t.problemdetail = pd.id AND t.custinfo = ci.id AND u.id = t.user AND t.ticketstatus = ts.id AND t.level = l.id AND t.created BETWEEN :startDate AND :endDate AND th.ticket = t.id GROUP BY th.ticket'
        ;
        $res = $req->getConnection()->prepare($fetchData);
        $res->bindParam('startDate', $startDate);
        $res->bindParam('endDate', $endDate);
        $res->execute();
        
        $response = ($res->fetchAll());

                return $this->json(array(
                    'response' => $response,
                ));
    }

    // ========================================================
    // ========================================================
    // ======== TICKET STATUS PIE CHART CONTROLLER ============
    // ========================================================
    // ========================================================

        #[Route('/dashboard/ticketpiechart/{problemName}/{detailName}')]
        public function ticketPieChart(Request $request, $problemName, $detailName) {

            $req = $this->getDoctrine()->getManager();

            $fetchProblems = $req->createQuery(
                'SELECT p.name AS name, p.id AS id
                    FROM App\Entity\Problem p'
            );
            $problemList = $fetchProblems->getResult();

            if($problemName != '%') {
            $fetchDetails = $req->createQuery(
                'SELECT pd.name AS name, pd.id AS id, p.id as parent
                    FROM App\Entity\Problem p, App\Entity\ProblemDetail pd
                        WHERE pd.problem = p.id AND p.id LIKE :problemId' 
            );
            $fetchDetails->setParameter('problemId', $problemName);
            $detailList = $fetchDetails->getResult();
            }

            $openTicket = $req->createQuery(
                'SELECT COUNT(t.id) AS countOpenTickets, p.name AS ProblemName, pd.name AS DetailName
                    FROM App\Entity\Ticket t, App\Entity\Problem p, App\Entity\ProblemDetail pd
                        WHERE t.ticketstatus = 1 AND t.problem = p.id AND t.problemdetail = pd.id AND p.id LIKE :problemId AND pd.id LIKE :problemDetailId'
            );
            $openTicket->setParameter('problemId', $problemName)
                        ->setParameter('problemDetailId', $detailName);
            $responseOpen = $openTicket->getResult();

            $closedTicket = $req->createQuery(
                'SELECT COUNT(t.id) AS countClosedTickets, p.name AS ProblemName, pd.name AS DetailName
                    FROM App\Entity\Ticket t, App\Entity\Problem p, App\Entity\ProblemDetail pd
                        WHERE t.ticketstatus = 2 AND t.problem = p.id AND t.problemdetail = pd.id AND p.id LIKE :problemId AND pd.id LIKE :problemDetailId'
            );
            $closedTicket->setParameter('problemId', $problemName)
                        ->setParameter('problemDetailId', $detailName);
            $responseClosed = $closedTicket->getResult();

            $showOpen = floatval($responseOpen[0]['countOpenTickets']);
            $showClosed = floatval($responseClosed[0]['countClosedTickets']);
            if($problemName === '%') {
                $problemName = 'All';
            } else {
                $problemName = $responseOpen[0]['ProblemName'];
            }
            if($detailName === '%') {
                $detailName = 'All';
            } else {
                $detailName = $responseOpen[0]['DetailName'];
            }

            return $this->json(array('problems' => $problemList,
                                    'details' => $detailList,
                                    'openTickets' => $showOpen,
                                    'closedTickets' => $showClosed,
                                    'postedData' => $problemName,
                                    'postedData2' => $detailName,
            ));
        }

    // ========================================================
    // ========================================================
    // ================ 24 HOUR GRID ==========================
    // ========================================================
    // ========================================================

    #[Route('/dashboard/hourlygrid/{user}/{startDate}/{endDate}')]

    public function hourlyGrid(Request $request, $user, $startDate, $endDate) {

        $req = $this->getDoctrine()->getManager();

        $fetchUsers = $req->createQuery(
            'SELECT u.name AS name, u.id AS id
                FROM App\Entity\Users u
                    WHERE u.employe = 1 AND u.enabled = 1 ORDER BY name ASC'
        );
        $userList = $fetchUsers->getResult();
        
        $openTickets = 'SELECT th.ticket, u.name AS Employee_Name, th.updated as Updated_At, HOUR(th.updated) as hourOpened, COUNT(HOUR(th.    updated)) AS totalOpened
                        FROM ticket_history th, users u
                        WHERE th.id IN ( SELECT min(id) FROM ticket_history GROUP BY ticket ) AND th.ticketstatus = 1 AND u.id = th.user AND u.employe = 1 AND u.name LIKE :userId AND th.updated BETWEEN :startDate AND :endDate GROUP BY HOUR(th.updated) ORDER BY hourOpened';
        $openReq = $req->getConnection()->prepare($openTickets);
        $openReq->bindParam('userId', $user);
        $openReq->bindParam('startDate', $startDate);
        $openReq->bindParam('endDate', $endDate);
        $openReq->execute();
        $openTicketsResult = ($openReq->fetchAll());

        $closedTickets = 'SELECT th.ticket, u.name AS Employee_Name, th.updated as Updated_At, HOUR(th.updated) as hourClosed, COUNT(HOUR(th.  updated)) AS totalClosed
                        FROM ticket_history th, users u
                        WHERE th.id IN ( SELECT max(id) FROM ticket_history GROUP BY ticket ) AND th.ticketstatus = 2 AND u.id = th.user AND u.employe = 1 AND u.name LIKE :userId AND th.updated BETWEEN :startDate AND :endDate GROUP BY HOUR(th.updated) ORDER BY hourClosed';
        $closeReq = $req->getConnection()->prepare($closedTickets);
        $closeReq->bindParam('userId', $user);
        $closeReq->bindParam('startDate', $startDate);
        $closeReq->bindParam('endDate', $endDate);
        $closeReq->execute();
        $closedTicketsResult = ($closeReq->fetchAll());

        return $this->json(array(
            'users' => $userList,
            'openTickets' => $openTicketsResult,
            'closedTickets' => $closedTicketsResult

        ));
    }


    // ========================================================
    // ========================================================
    // ======== COMPANY->PROBLEM GRAPH CONTROLLER =============
    // ========================================================
    // ========================================================

    #[Route('/dashboard/companygraph/{problemName}/{detailName}/{companyName}/{startDate}/{endDate}')]
    public function companyGraph(Request $request, $problemName, $detailName, $companyName, $startDate, $endDate) {
        $req = $this->getDoctrine()->getManager();

        $fetchCompany = 
            'SELECT ci.acct_name AS companyName, ci.id AS id
                FROM cust_info ci ORDER BY companyName ASC';
        $companies = $req->getConnection()->prepare($fetchCompany);
        $companies->execute();
        $companyList = ($companies->fetchAll());

        $openTickets =
            'SELECT th.ticket AS openTickets, p.name AS problemName, pd.name AS detailName, DATE(th.updated) as dateOpened, ci.acct_name AS companyName, ts.id AS ticketStatus, ts.name AS ticketStatusName, COUNT(DATE(th.updated)) AS totalOpened
                FROM ticket t, problem p, problem_detail pd, cust_info ci, ticket_status ts, ticket_history th
                    WHERE th.id IN ( SELECT min(id) FROM ticket_history GROUP BY ticket ) AND ts.id = 1 AND t.id = th.ticket AND t.custinfo = ci.id AND ts.id = t.ticketstatus AND t.problem = p.id AND t.problemdetail = pd.id AND p.name LIKE :problemName AND pd.name LIKE :detailName AND ci.acct_name LIKE :companyName AND th.updated BETWEEN :startDate AND :endDate GROUP BY DATE(th.updated)';
        $openReq = $req->getConnection()->prepare($openTickets);
        $openReq->bindParam('problemName', $problemName);
        $openReq->bindParam('detailName', $detailName);
        $openReq->bindParam('companyName', $companyName);
        $openReq->bindParam('startDate', $startDate);
        $openReq->bindParam('endDate', $endDate);
        $openReq->execute();
        $openTicketsResult = ($openReq->fetchAll());

        $closedTickets = 'SELECT th.ticket AS closedTickets, p.name AS problemName, pd.name AS detailName, ci.acct_name AS companyName, ts.id AS ticketStatus, ts.name AS ticketStatusName, DATE(th.updated) AS dateClosed, COUNT(DATE(th.updated)) AS totalClosed
            FROM ticket t, problem p, problem_detail pd, cust_info ci, ticket_status ts, ticket_history th
                WHERE th.id IN ( SELECT max(id) FROM ticket_history GROUP BY ticket ) AND t.ticketstatus = 2 AND t.id = th.ticket AND t.custinfo = ci.id AND ts.id = t.ticketstatus AND t.problem = p.id AND t.problemdetail = pd.id AND p.name LIKE :problemName AND pd.name LIKE :detailName AND ci.acct_name LIKE :companyName AND th.updated BETWEEN :startDate AND :endDate GROUP BY DATE(th.updated)';
        $closedReq = $req->getConnection()->prepare($closedTickets);
        $closedReq->bindParam('problemName', $problemName);
        $closedReq->bindParam('detailName', $detailName);
        $closedReq->bindParam('companyName', $companyName);
        $closedReq->bindParam('startDate', $startDate);
        $closedReq->bindParam('endDate', $endDate);
        $closedReq->execute();
        $closedTicketsResult = ($closedReq->fetchAll());

        return $this->json(array(
            'companyList' => $companyList,
            'openTickets' => $openTicketsResult,
            'closedTickets' => $closedTicketsResult
        ));

    }


    // ========================================================
    // ========================================================
    // ======== DAILY TICKET TRACKER CONTROLLER ===============
    // ========================================================
    // ========================================================

    #[Route('/dashboard/dailyTicketTracker')]
    public function dailyTicketTracker(Request $request) {
        $req = $this->getDoctrine()->getManager();

        $dev = true;
        $startDate = new \DateTime('now');
        $startDate = date_modify($startDate, '-100 day');
        $endDate = new \DateTime('now');
        if($dev) {
            $problem = 1;
            $problemDetail = 1;
            $companyName = 49;
        } else {
            $problem = '%';
            $problemDetail = '%';
            $companyName = '%';
        }

        $dailyOpenTicketTracker = $req->createQuery(
            'SELECT COUNT(t.id) AS countOpenTickets, p.name AS problemName, pd.name AS detailName, ci.acct_name AS companyName
                FROM App\Entity\Ticket t, App\Entity\Problem p, App\Entity\ProblemDetail pd, App\Entity\CustInfo ci
                    WHERE t.created BETWEEN :startDate AND :endDate AND t.ticketstatus = 1 AND t.custinfo = ci.id AND t.problem = p.id AND t.problemdetail = pd.id AND p.id LIKE :problemId AND pd.id LIKE :problemDetailId AND ci.id LIKE :companyName'
        );
        $dailyOpenTicketTracker->setParameter('problemId', $problem)
                    ->setParameter('problemDetailId', $problemDetail)
                    ->setParameter('companyName', $companyName)
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
        $responseOpen = $dailyOpenTicketTracker->getResult();

        $dailyClosedTicketTracker = $req->createQuery(
            'SELECT COUNT(t.id) AS countClosedTickets, p.name AS problemName, pd.name AS detailName, ci.acct_name AS companyName
                FROM App\Entity\Ticket t, App\Entity\Problem p, App\Entity\ProblemDetail pd, App\Entity\CustInfo ci
                    WHERE t.created BETWEEN :startDate AND :endDate AND t.ticketstatus = 2 AND t.custinfo = ci.id AND t.problem = p.id AND t.problemdetail = pd.id AND p.name LIKE :problemId AND pd.name LIKE :problemDetailId AND ci.id LIKE :companyName'
        );
        $dailyClosedTicketTracker->setParameter('problemId', $problem)
                    ->setParameter('problemDetailId', $problemDetail)
                    ->setParameter('companyName', $companyName)
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
        $responseClosed = $dailyClosedTicketTracker->getResult();

        $res = new JsonResponse();
        $res->headers->set('Content-Type', 'application/json');
        $res->setData(array('ticketList' => $dailyOpenTicketTracker));
        echo '<p>There are have been ' . $responseOpen[0]['countOpenTickets'] . ' tickets opened and ' . $responseClosed[0]['countClosedTickets'] . ' tickets closed for ' . $responseOpen[0]['companyName'] . ' that are due to ' . $responseOpen[0]['problemName'] . ' - ' . $responseOpen[0]['detailName'] . ' between ' . $startDate->format('d-m-Y') . ' and ' . $endDate->format('d-m-Y');
        die();
    }


    // ========================================================
    // ========================================================
    // ======== EMPLOYEE PERFORMANCE CONTROLLER ===============
    // ========================================================
    // ========================================================

    #[Route('/dashboard/employeePerformanceTracker')]
    public function employeePerformanceTracker(Request $request) {
        $req = $this->getDoctrine()->getManager();

        
        // $employeePerformanceTracker = $req->createQuery(
        //     'SELECT COUNT(th.id) AS countOpenedTickets, u.name AS employeeName
        //         FROM App\Entity\TicketHistory th, App\Entity\Users u
        //         WHERE th.updated BETWEEN :startDate AND :endDate AND th.id IN ( SELECT min(id) FROM App\Entity\TicketHistory GROUP BY th.ticket ) AND th.user LIKE :user and th.ticketstatus = 1 AND u.id = th.user AND u.employe = 1'
        // );
        // $employeePerformanceTracker->setParameter('user', $user)
        //             ->setParameter('startDate', $startDate)
        //             ->setParameter('endDate', $endDate);

        //$conn = $this->getDoctrine()->getManager()->getConnection();

        $dev = true;
        $startDate = new \DateTime('now');
        $startDate = date_modify($startDate, '-30 day');
        $start = $startDate->format('Y-m-d H:i:s');
        $endDate = new \DateTime('now');
        $end = $endDate->format('Y-m-d H:i:s');
        if($dev) {
            $user = 9;
        } else {
            $user = '%';
        }

        $open = 'SELECT count(th.id) AS totalTicketsOpened, u.name AS employeeName, u.employe AS Is_Employee
        FROM ticket_history th, users u
        WHERE th.updated BETWEEN :startDate AND :endDate AND th.id IN ( SELECT min(id) FROM ticket_history GROUP BY ticket ) AND th.user LIKE :userId AND th.ticketstatus = 1 AND u.id = th.user AND u.employe = 1';

        $openReq = $req->getConnection()->prepare($open);
        $openReq->bindParam('startDate', $start);
        $openReq->bindParam('endDate', $end);
        $openReq->bindParam('userId', $user);
        $openReq->execute();
        $openTickets = ($openReq->fetchAll());

        $close = 'SELECT count(th.id) AS totalTicketsClosed, u.name AS employeeName, u.employe AS Is_Employee
        FROM ticket_history th, users u
        WHERE th.updated BETWEEN :startDate AND :endDate AND th.id IN ( SELECT max(id) FROM ticket_history GROUP BY ticket ) AND th.user LIKE :userId AND th.ticketstatus = 2 AND u.id = th.user AND u.employe = 1';

        $closeReq = $req->getConnection()->prepare($close);
        $closeReq->bindParam('startDate', $start);
        $closeReq->bindParam('endDate', $end);
        $closeReq->bindParam('userId', $user);
        $closeReq->execute();
        $closedTickets = ($closeReq->fetchAll());
        $res = new JsonResponse();
        $res->headers->set('Content-Type', 'application/json');
        $res->setData(array('ticketList' => $openTickets, $closedTickets));
        echo '<p>' . $openTickets[0]['employeeName'] . ' has opened ' . $openTickets[0]['totalTicketsOpened'] . ' ticket and closed ' . $closedTickets[0]['totalTicketsClosed'] . ' ticket between ' . $startDate->format('d-m-Y') . ' and ' . $endDate->format('d-m-Y') . '</p>';
        die;
    }
}