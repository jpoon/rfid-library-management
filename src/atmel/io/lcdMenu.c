#include "lcd.h"
#include "lcdMenu.h"
#include "book.h"
#include "rfid.h"
#include "Security.h"
#include <avr/pgmspace.h> 

static void userSignOut( void );

const char mainMenuStr[] PROGMEM="Main Menu\0";
const char findBookStr[] PROGMEM="Find Book\0";
const char librarianStr[] PROGMEM="Librarian\0";
const char secureGateStr[] PROGMEM="Secure Gate\0";
const char misplacedStr[] PROGMEM="Misplaced\0";
const char checkInStr[] PROGMEM="Check In\0";
const char checkOutStr[] PROGMEM="Check Out\0";
const char signOutStr[] PROGMEM="Sign Out\0";

const char aboutStr[] PROGMEM="About Us\0";
const char adnanStr[] PROGMEM="Adnan J.\0";
const char jasonStr[] PROGMEM="Jason P.\0";
const char jayStr[] PROGMEM="Jay W.\0";
const char manasiStr[] PROGMEM="Manasi K.\0";
const char mohammedStr[] PROGMEM="Mohammed T.\0";

const char errorStr[] PROGMEM="Error\0";
const char insufficientStr[] PROGMEM="Insufficient\0";
const char credentialsStr[] PROGMEM="Credentials\0";

const char starStr[] PROGMEM="*\0";

const char toStartStr[] PROGMEM="to start\0";
const char scanningBookStr[] PROGMEM="scanning books\0";
const char scanningStr[] PROGMEM="Scanning...\0";
const char scanEnterStr[] PROGMEM="Scan:   Enter\0";
const char exitBackStr[] PROGMEM="Exit:   Back\0";
const char checkedInStr[] PROGMEM="Checked In\0";
const char checkedOutStr[] PROGMEM="Checked Out\0";
const char pressEnterStr[] PROGMEM="Press enter\0";
const char toContinueStr[] PROGMEM="to continue\0";
const char scanningBooksStr[] PROGMEM="Scanning books:\0";
const char pressBackStr[] PROGMEM="Press back\0";
const char toExitStr[] PROGMEM="to exit\0";
const char waitingForStr[] PROGMEM="Waiting for\0";
const char signingInStr[] PROGMEM="sign in...\0";
const char loggingOutStr[] PROGMEM="Logging Out...\0";
const char toContinue[] PROGMEM="to continue\0";

menu mainMenu;
/* About Us */
static menuItems aboutUsMenuItems[] =
{
   	{adnanStr, 		NULL, 		NULL},
    {jasonStr, 		NULL, 		NULL},
    {jayStr, 		NULL, 		NULL},
    {manasiStr, 	NULL, 		NULL},
    {mohammedStr, 	NULL, 		NULL},
};
static menu aboutMenu = {aboutStr, 5, aboutUsMenuItems, &mainMenu};

/* Librarian */
static menuItems librarianMenuItems[] =
{
    {secureGateStr,		NULL, 		&securityCheck},
    {misplacedStr, 		NULL, 		&bookFindMisplaced},
    {checkInStr, 		NULL, 		&bookCheckIn}, 
};
static menu librarianMenu= {librarianStr, 3, librarianMenuItems, &mainMenu};

/* Find Menu */
menu findMenu= {findBookStr, 1, NULL, &mainMenu};

/* Main Menu */
static menuItems mainMenuItems[] =
{
    {findBookStr, 	&findMenu,		NULL},
   	{checkOutStr, 	NULL, 			&bookCheckOut},
   	{librarianStr, 	&librarianMenu,	NULL},
   	{signOutStr,	NULL,			&userSignOut},   	
   	{aboutStr, 		&aboutMenu, 	NULL},   	
};
menu mainMenu = {mainMenuStr, 5, mainMenuItems, NULL};


static volatile int8_t currentLine;      	/* current selected line */
volatile menu *pCurrentMenu;      			/* current menu */
static volatile BOOL userBusy;				/* busy flag */

volatile BOOL userCancelRequest;			/* user has cancelled previous request */
volatile BYTE user_auth;					/* User Authentication Level */

extern book *pBookListHead;     			/* books list (defined in book.c)*/

void menuInit( void ) {
	pCurrentMenu = &mainMenu;
	currentLine = 0;
	user_auth = USER_AUTH_UN_INIT;	
	userCancelRequest = FALSE;
	userBusy = FALSE;
	menuBuild(0);
}

void menuBuild( int8_t change ) {
    BYTE disp;
    BYTE cnt;
	book *pNode = pBookListHead;

	if ( userBusy ) return;
	
	if ( user_auth == USER_AUTH_UN_INIT ) {
		lcdClear();
		lcdGoTo(1,1);
		lcdFlashPrint(waitingForStr);
		lcdGoTo(2,3);
		lcdFlashPrint(signingInStr);
		return;
	}
		
	currentLine += change;

	if (currentLine < 0) {
		currentLine = 0;
		return;
	}

	if (currentLine > pCurrentMenu->numItems-1) {
		currentLine = pCurrentMenu->numItems-1;
		return;
	}

	lcdClear();
	lcdFlashPrint(pCurrentMenu->title);

	if ( pCurrentMenu == &librarianMenu && user_auth != USER_AUTH_LIBRARIAN ) {
		lcdGoTo(1,2);
		lcdFlashPrint(errorStr);
		lcdGoTo(2,3);
		lcdFlashPrint(insufficientStr);		
		lcdGoTo(3,3);
		lcdFlashPrint(credentialsStr);				
		return;
	}

	disp = currentLine/3;

    for (cnt = 0; cnt < (disp*3); cnt++) {
		pNode = pNode->pNext;
    }

	for (cnt = 0; (cnt < 3) && (disp*3+cnt < pCurrentMenu->numItems); cnt++) {
		lcdGoTo(cnt+1,2);

        if ( pCurrentMenu == &findMenu ) {
            if (pNode == NULL) break;
            printf("%s", pNode->title);            
            pNode = pNode->pNext;
        } else {
		    lcdFlashPrint(pCurrentMenu->pMenuItems[disp*3+cnt].title);
        }
    }

	lcdGoTo((currentLine % 3)+1, 0);
	lcdFlashPrint(starStr);

	lcdGoTo((currentLine % 3)+1, 15);
	lcdFlashPrint(starStr);
}

void menuBack( void ) {
	userCancelRequest = TRUE;	
	if ( userBusy ) {
		/* if currently processing something, cancel it */
		userBusy = FALSE;
	} else {
		/* else go to the previous menu */
	    if ( pCurrentMenu->prevMenu != NULL ) pCurrentMenu = pCurrentMenu->prevMenu;
	}
    currentLine = 0;
    menuBuild(0);
}

void menuExecute( void ) {
    BYTE cnt;
	book *pNode = pBookListHead;

	userCancelRequest = FALSE;
	
	if ( user_auth == USER_AUTH_UN_INIT ) return;

	if ( !userBusy ) {
	    if ( pCurrentMenu == &findMenu ) {
			/* Execute Find Book */
	        lcdClear();
			lcdFlashPrint(pCurrentMenu->title);

			lcdGoTo(1,1);
	        for (cnt = 0; cnt < currentLine; cnt++) {
				/* Iterate through book list */
				pNode = pNode->pNext;
			}
			
            if (pNode == NULL) return;

	        currentLine = 0;

            userBusy = TRUE;
		    bookFind(pNode->rfid, pNode->shelfId);
		} else if ( pCurrentMenu->pMenuItems[currentLine].subMenu != NULL ) {
	    	/* Build Sub Menu */
			pCurrentMenu = pCurrentMenu->pMenuItems[currentLine].subMenu;
	        currentLine = 0;
	        menuBuild(0);
		} else if ( pCurrentMenu->pMenuItems[currentLine].command != NULL ) {

			if ( pCurrentMenu == &librarianMenu ) {
				/* check credentials before executing librarian functions */
				if ( user_auth != USER_AUTH_LIBRARIAN ) return;
			}
			
			/* Execute Function */
	        lcdClear();
			lcdFlashPrint(pCurrentMenu->pMenuItems[currentLine].title);
			lcdGoTo(1,1);

			userBusy = TRUE;
			pCurrentMenu->pMenuItems[currentLine].command();
		}
    	userBusy = FALSE;
	} else {
		buttonEnter = TRUE;
	}
}

static void userSignOut( void ) {
	bookInit();	
	user_auth = USER_AUTH_UN_INIT;

	lcdClear();
	lcdGoTo(0,1);
	lcdFlashPrint(loggingOutStr);
	lcdGoTo(2,2);
	lcdFlashPrint(pressBackStr);
	lcdGoTo(3,2);
	lcdFlashPrint(toContinue);

	currentLine = 0;
}

