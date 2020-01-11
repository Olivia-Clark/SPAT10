<?php
/*
 * created by Michael Jarratt
 *
 * this class is responsible for providing a simple API to retrieve information needed
 * to create the report from the database
 */

require_once __DIR__."/AuditQuery.php";
require_once __DIR__."/UserQuery.php";
require_once __DIR__."/QuestionQuery.php";
require_once __DIR__."/Database.php"; //temporary to make queries faster MUST REMOVE and separate queries into their classes

class ReportInfoGetter
{
    private $auditQuery;
    private $userQuery;
    private $questionQuery;

    public function __construct()
    {
        $this->auditQuery = new AuditQuery();
        $this->userQuery = new UserQuery();
        $this->questionQuery = new QuestionQuery();
    }

    /*
     * gets all information needed to create a report for specific audit
     * and returns it in an associatively index array
     */
    public function getAudit($clientID, $auditID)
    {
        $reportInfo = []; //array in which information will be gathered to be passed out
        $reportInfo['audit'] = $this->auditQuery->getAudit($auditID); //location, date scored
        $reportInfo['user'] = $this->userQuery->getUsername($clientID); //name of client
        //$reportInfo['subCatDescription'] = $this->getSubcatagory($catID);//gets subcatagory name
        $reportInfo['questions'] = $this->questionQuery->getAuditQuestions($auditID); //Questions
        //$reportInfo['score'] = $this->ScoreQuery->getScores($auditID); //Scores
        //$reportInfo['comment'] = $this->CommentQuery->getComments($auditID);// comments scorer made
        //$reportInfo['complianceBand'] = $this->ComplianceQuery->getComplianceBand($percentileID);// compliance band

        $database = Database::getInstance();
        //Question IDs
        $questionIDs = $this->questionQuery->getQuestionIDs($auditID);
        $questionIDs = join(",",$questionIDs); //turns array into comma separated list

        //SubCategory IDs
        $subcatIDs = $database->retrieve("SELECT DISTINCT subCatID FROM Questions
                                                WHERE questionID IN ($questionIDs)");
        $temp = [];
        foreach ($subcatIDs as $tuple)
        {
            array_push($temp, $tuple['subCatID']); //extracts subCatIDs from tuples
        }
        $subcatIDs = $temp;
        $subcatIDs = join(",",$subcatIDs); //turns into a comma seporated list

        //Category IDs
        $catiDs = $database->retrieve("SELECT DISTINCT catID FROM SubCategories WHERE subCatID in (\"$subcatIDs\")");
        var_dump($catiDs);
        return $reportInfo;
    }
}