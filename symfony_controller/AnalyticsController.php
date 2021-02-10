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
    // ======== TICKET TRACKER CONTROLLER =====================
    // ========================================================
    // ========================================================

    #[Route('/dashboard/ticketTracker')]
    public function ticketTracker(Request $request) {
        $req = $this->getDoctrine()->getManager();
        $dateFormat = date("Y-m-d h:i:s");
        $getStartDate = new \DateTime("2019-11-13 00:00:00");
        $today = new \DateTime('now');

        $openTicket = $req->createQuery(
            'SELECT COUNT(t.id) AS openTicketsOverPeriod
                FROM App\Entity\Ticket t
                WHERE t.created BETWEEN :getStartDate AND :today'
        );
        $openTicket->setParameter('getStartDate', $getStartDate->format('Y-m-d h:i:s'))
                    ->setParameter('today', $today->format('Y:m:d h:i:s'));
        $resOpen = $openTicket->getResult();

        $closedTickets = $req->createQuery(
            'SELECT COUNT(t.id) AS closedTicketsOverPeriod
            FROM App\Entity\Ticket t
            WHERE t.ticketstatus = 2 AND t.created between :getStartDate AND :today'
        );
        $closedTickets->setParameter('getStartDate', $getStartDate->format('Y-m-d h:i:s'))
                    ->setParameter('today', $today->format('Y:m:d h:i:s'));
        $resClosed = $closedTickets->getResult();

        $res = new JsonResponse();
        $res->headers->set('Content-Type', 'application/json');
        $res->setData(array('openTickets' => $resOpen, $resClosed));
        echo '<p>A total of <strong>' . $resOpen[0]['openTicketsOverPeriod'] . '</strong> have been opened between ' . $getStartDate->format('d-m-Y') . ' and ' . $today->format('d-m-Y') . ' !</p>';

        echo '<p>A total of <strong>' . $resClosed[0]['closedTicketsOverPeriod'] . '</strong> have been closed between ' . $getStartDate->format('d-m-Y') . ' and ' . $today->format('d-m-Y') . ' !</p>';

        echo '<p>This means that, over the period, ' . round($resClosed[0]['closedTicketsOverPeriod']/$resOpen[0]['openTicketsOverPeriod'], 4)*100 . '% of all tickets have been closed !';

        die();
    }


    // ========================================================
    // ========================================================
    // ======== COMPANY->PROBLEM GRAPH CONTROLLER =============
    // ========================================================
    // ========================================================

    #[Route('/dashboard/companyGraph')]
    public function companyGraph(Request $request) {
        $req = $this->getDoctrine()->getManager();

        // $problem = $request->request->get('problem');
        // $problemDetail = $request->request->get('problemDetail');
        // $companyName = $request->request->get('companyName');
        // $ticketStatus = $request->request->get('ticketStatus');

        $dev = true;

        if($dev) {
            $problem = 1;
            $problemDetail = 1;
            $companyName = 49;
            $ticketStatus = 2;
        } else {
            $problem = '%';
            $problemDetail = '%';
            $companyName = '%';
            $ticketStatus = '%';
        }
        $graphRequest = $req->createQuery(
            'SELECT COUNT(t.id) AS countTickets, p.name AS problemName, pd.name AS detailName, ci.acct_name AS companyName, ts.id AS ticketStatus, ts.name AS ticketStatusName
                FROM App\Entity\Ticket t, App\Entity\Problem p, App\Entity\ProblemDetail pd, App\Entity\CustInfo ci, App\Entity\TicketStatus ts
                    WHERE t.custinfo = ci.id AND ts.id = t.ticketstatus AND t.problem = p.id AND t.problemdetail = pd.id AND p.id LIKE :problemId AND pd.id LIKE :problemDetailId AND ci.id LIKE :companyName AND ts.id LIKE :ticketStatus'
        );
        $graphRequest->setParameter('problemId', $problem)
                    ->setParameter('problemDetailId', $problemDetail)
                    ->setParameter('companyName', $companyName)
                    ->setParameter('ticketStatus', $ticketStatus);
        $responseOpen = $graphRequest->getResult();

        $res = new JsonResponse();
        $res->headers->set('Content-Type', 'application/json');
        $res->setData(array('ticketList' => $graphRequest));
        echo '<p>There are currently ' . $responseOpen[0]['countTickets'] . ' ' . strtolower($responseOpen[0]['ticketStatusName']) . ' tickets for ' . $responseOpen[0]['companyName'] . ' that are due to ' . $responseOpen[0]['problemName'] . ' - ' . $responseOpen[0]['detailName'];
        die();
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
                    WHERE t.created BETWEEN :startDate AND :endDate AND t.ticketstatus = 2 AND t.custinfo = ci.id AND t.problem = p.id AND t.problemdetail = pd.id AND p.id LIKE :problemId AND pd.id LIKE :problemDetailId AND ci.id LIKE :companyName'
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