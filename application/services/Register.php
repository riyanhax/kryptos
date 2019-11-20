<?php

class Application_Service_Register
{
    /** @var self */
    protected static $_instance = null;

    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    public function getDefaultRights(){
        return '{"perm\/config":1,"perm\/config\/company-information":1,"perm\/config\/logs":1,"perm\/documents":1,"perm\/documents\/all":1,"perm\/documents\/update":1,"perm\/documents\/remove-all":0,"perm\/documentsversioned":1,"perm\/documentsversioned\/create":1,"perm\/documentsversioned\/update":1,"perm\/documentsversioned\/remove":0,"perm\/documentsversioned\/version-create":1,"perm\/documentsversioned\/version-update":1,"perm\/documentsversioned\/version-remove":1,"perm\/documenttemplates":1,"perm\/documenttemplates\/create":1,"perm\/documenttemplates\/update":1,"perm\/documenttemplates\/remove":0,"perm\/groups":1,"perm\/groups\/create":0,"perm\/groups\/update":0,"perm\/groups\/remove":0,"perm\/sites":1,"perm\/sites\/create":1,"perm\/sites\/update":1,"perm\/sites\/remove":0,"perm\/course-categories":1,"perm\/course-categories\/create":1,"perm\/course-categories\/update":1,"perm\/course-categories\/remove":0,"perm\/exam-categories":1,"perm\/exam-categories\/create":1,"perm\/exam-categories\/update":1,"perm\/exam-categories\/remove":0,"perm\/kominfoadm":1,"perm\/kominfoadm\/all":1,"perm\/kominfoadm\/create":1,"perm\/kominfoadm\/remove":0,"perm\/kominfoadm\/send":1,"perm\/tickets":1,"perm\/tickets\/create":1,"perm\/tickets\/update":1,"perm\/tickets\/remove":1,"perm\/tickets\/assignees":1,"perm\/tickets\/allaccess":1,"perm\/tickets\/config":1,"perm\/admin":1,"perm\/shared-users":1,"perm\/shared-users\/create":1,"perm\/shared-users\/update":1,"perm\/shared-users\/remove":0,"perm\/podpisy":1,"perm\/podpisy\/create":1,"perm\/podpisy\/update":1,"perm\/podpisy\/remove":1,"perm\/podpisy\/osoby":1,"perm\/osoby":1,"perm\/osoby\/create":1,"perm\/osoby\/update":1,"perm\/osoby\/remove":1,"perm\/osoby\/report":1,"perm\/osoby\/set-permissions":1,"perm\/osoby\/set-upowaznienia":1,"perm\/osoby\/set-klucze":1,"perm\/osoby\/admin":1,"perm\/osoby\/proposal_add":1,"perm\/permissions":1,"perm\/permissions\/create":1,"perm\/permissions\/update":1,"perm\/permissions\/remove":0,"perm\/user-profile":1,"perm\/user-profile\/logs":1,"perm\/registry":1,"perm\/registry\/all-access":1,"perm\/zbiory-changelog":1,"perm\/zbiory-changelog\/create":1,"perm\/zbiory-changelog\/update":1,"perm\/zbiory-changelog\/remove":1,"perm\/numberingschemes":1,"perm\/numberingschemes\/create":1,"perm\/numberingschemes\/update":1,"perm\/numberingschemes\/remove":0,"perm\/systemy-teleinformacyjne":1,"perm\/systemy-teleinformacyjne\/create":1,"perm\/systemy-teleinformacyjne\/update":1,"perm\/systemy-teleinformacyjne\/remove":0,"perm\/courses":1,"perm\/courses\/create":1,"perm\/courses\/update":1,"perm\/courses\/remove":0,"perm\/exams":1,"perm\/exams\/create":1,"perm\/exams\/update":1,"perm\/exams\/remove":0,"perm\/verifications":1,"perm\/verifications\/create":1,"perm\/verifications\/remove":0,"perm\/verifications\/actions":1,"perm\/proposals":1,"perm\/proposals\/create":1,"perm\/proposals\/update":1,"perm\/proposals\/remove":0,"perm\/tasks":0,"perm\/public-procurements":1,"perm\/public-procurements\/create":1,"perm\/public-procurements\/update":1,"perm\/public-procurements\/remove":1,"perm\/public-procurements\/remove-file":1,"perm\/zbiory":1,"perm\/zbiory\/edit":1,"perm\/zbiory\/remove":0,"perm\/zbiory\/report":1,"perm\/zbiory\/fields":1,"perm\/zbiory\/pomieszczenia":1,"perm\/zbiory\/legalacts":1,"perm\/zbiory\/zabezpieczenia":1,"perm\/legalacts":1,"perm\/legalacts\/create":1,"perm\/legalacts\/update":1,"perm\/legalacts\/remove":0,"perm\/budynki":1,"perm\/budynki\/create":1,"perm\/budynki\/update":1,"perm\/budynki\/remove":0,"perm\/fielditems":1,"perm\/fielditems\/create":1,"perm\/fielditems\/update":1,"perm\/fielditems\/remove":0,"perm\/fielditems\/unlock":1,"perm\/fielditemscategories":1,"perm\/fielditemscategories\/create":1,"perm\/fielditemscategories\/update":1,"perm\/fielditemscategories\/remove":0,"perm\/fielditemscategories\/unlock":1,"perm\/fieldscategories":1,"perm\/fieldscategories\/create":1,"perm\/fieldscategories\/update":1,"perm\/fieldscategories\/remove":0,"perm\/fieldscategories\/unlock":1,"perm\/persons":1,"perm\/persons\/create":1,"perm\/persons\/update":1,"perm\/persons\/remove":0,"perm\/persons\/unlock":1,"perm\/fields":1,"perm\/fields\/create":1,"perm\/fields\/update":1,"perm\/fields\/remove":0,"perm\/fields\/unlock":1,"perm\/pomieszczenia":1,"perm\/pomieszczenia\/create":1,"perm\/pomieszczenia\/update":1,"perm\/pomieszczenia\/remove":0,"perm\/persontypes":1,"perm\/persontypes\/create":1,"perm\/persontypes\/update":1,"perm\/persontypes\/remove":0,"perm\/persontypes\/unlock":1,"perm\/zabezpieczenia":1,"perm\/zabezpieczenia\/create":1,"perm\/zabezpieczenia\/update":1,"perm\/zabezpieczenia\/remove":0,"perm\/file-sources":1}';
    }
}