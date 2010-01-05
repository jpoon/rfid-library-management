#include "book.h"
#include "zigbee.h"
#include "rfid.h"
#include <stdlib.h> 
#include "util.h"
#include "lcdMenu.h"
#include "lcd.h"
#include "security.h"

extern menu findMenu;
book *pBookListHead;
volatile BYTE bookStatus;

static BYTE * scanBook( void );

void bookInit( void ) {	
	book *pNode = NULL;	
	
	while( pBookListHead != NULL ) {
		pNode = pBookListHead->pNext;
		free(pBookListHead);
		pBookListHead = pNode;
	}

    pBookListHead = NULL;
}

void bookListBuild( const BYTE bookNum, BYTE * const pBuf ) {
	BYTE j;
	BYTE bufCnt = 0;
	book *pNode = NULL;	

	if (bookNum == 1) {
		bookInit();
	}

	pNode = (book*)malloc(sizeof(book));
	if (pNode == NULL) {
#if DEBUG
		printf("book.c-malloc");
#endif
		return;
	}

	/* Book Title */
	for (j=0; j<ZIGBEE_BOOK_TITLE_SIZE; j++) {
		pNode->title[j] = *(pBuf+(bufCnt++));
	}
    pNode->title[j++] = '\0';

	/* RFID Tag */
	for (j=0; j<RFID_TAG_SIZE; j++) {
		pNode->rfid[j] = *(pBuf+(bufCnt++));
	}

	/* Shelf ID */
	for (j=0; j<ZIGBEE_SHELF_TITLE_SIZE; j++) {
		pNode->shelfId[j] = *(pBuf+(bufCnt++));
	}
	pNode->shelfId[j++] = '\0';

	pNode->pNext = pBookListHead;
	pBookListHead = pNode;

	findMenu.numItems = bookNum;
}

void bookFind ( BYTE * const bookTag, BYTE * const shelf ) {
	BOOL isFound = FALSE;
	
	lcdGoTo(1,1);
    printf("%s", shelf);
	lcdGoTo(2,4);
	lcdFlashPrint(pressEnterStr);
	lcdGoTo(3,4);
	lcdFlashPrint(toContinueStr);

	buttonEnter = FALSE;
	while(!buttonEnter) {
		/* wait for button press */
		if (userCancelRequest) return;
	}

	lcdClear();
	lcdFlashPrint(scanningStr);

	while(!userCancelRequest) {
		isFound = rfidFindBook(bookTag);
		if (userCancelRequest) return;

		lcdGoTo(2,1);
		if ( isFound ) {
			printf("Found!      ");
			soundAlarm(1000);
			break;
		} else {
			printf("keep walkin'");
		}
	}

	buttonEnter = FALSE;
	while(!buttonEnter) {
		if (userCancelRequest) return;
	}

	menuBuild(0);
}

void bookFindMisplaced( void ) {
	BYTE i;
	BYTE shelf[RFID_TAG_SIZE-1];
	BYTE *pShelf;

	lcdGoTo(1,1);
	printf("Scan Shelf:");

	lcdGoTo(2,2);
	lcdFlashPrint(scanEnterStr);
	lcdGoTo(3,2);
	lcdFlashPrint(exitBackStr);

	buttonEnter = FALSE;
	while(!buttonEnter) {
		if (userCancelRequest) return;
	}

	lcdClear();
	lcdFlashPrint(misplacedStr);
	lcdGoTo(2,2);
	lcdFlashPrint(scanningStr);
	
	pShelf = scanBook();
	for (i=0; i < RFID_TAG_SIZE; i++)
		shelf[i] = *(pShelf++);
		
	lcdClear();
	lcdFlashPrint(misplacedStr);
	lcdGoTo(1,2);
	lcdFlashPrint(pressEnterStr);
	lcdGoTo(2,2);
	lcdFlashPrint(toStartStr);
	lcdGoTo(3,2);	
	lcdFlashPrint(scanningBookStr);	

	buttonEnter = FALSE;
	while(!buttonEnter) {
		if (userCancelRequest) return;
	}

	lcdClear();
	lcdFlashPrint(misplacedStr);
	lcdGoTo(1,1);
	lcdFlashPrint(scanningBooksStr);
	lcdGoTo(2,3);
	lcdFlashPrint(pressBackStr);
	lcdGoTo(3,3);
	lcdFlashPrint(toExitStr);

	while (!userCancelRequest) {
		zigbeeSendFindMisplaced(ZIGBEE_CMD_MISPLACED_CHECK, shelf, rfidscanBook());
		delay_us(DELAY_ZIGBEE_SEND);
	}
	
	delay_ms(DELAY_ZIGBEE_SEND);
	zigbeeSendFindMisplaced(ZIGBEE_CMD_MISPLACED_END, NULL, NULL);
}

void bookCheckIn( void ) {
	BYTE *pTag;
	
	lcdGoTo(1,1);
	lcdFlashPrint(scanEnterStr);
	lcdGoTo(2,1);
	lcdFlashPrint(exitBackStr);

	while ( TRUE ) {
		buttonEnter = FALSE;
		while(!buttonEnter) {
			/* wait for button press */		
			if (userCancelRequest) return;
		}
		lcdClear();
		lcdFlashPrint(checkInStr);
		lcdGoTo(1,1);
		lcdFlashPrint(scanningStr);

		pTag = scanBook();
		zigbeeSendCmd(ZIGBEE_CMD_MOD_BOOK_REQ, BOOK_STATE_CHECKIN, pTag);

		while (!packetReceived) {
			/* Wait until packet has been received */				
			if (userCancelRequest) return;
		}
		packetReceived = FALSE;

		lcdGoTo(2,1);
		if (bookStatus == BOOK_RSP_SUCCESS) {
			lcdFlashPrint(checkedInStr);
		} else {
			lcdFlashPrint(errorStr);
		}
	}
}


void bookCheckOut( void ) {
	BYTE *pTag;
	
	lcdGoTo(1,1);
	lcdFlashPrint(scanEnterStr);
	lcdGoTo(2,1);
	lcdFlashPrint(exitBackStr);

	while ( TRUE ) {
		buttonEnter = FALSE;		
		while(!buttonEnter) {
			/* wait for button press */		
			if (userCancelRequest) return;
		}
		lcdClear();
		lcdFlashPrint(checkOutStr);
		lcdGoTo(1,1);
		lcdFlashPrint(scanningStr);

		pTag = scanBook();
		zigbeeSendCmd(ZIGBEE_CMD_MOD_BOOK_REQ, BOOK_STATE_CHECKOUT, pTag);

		while (!packetReceived) {
			/* Wait until packet has been received */				
			if (userCancelRequest) return;
		}
		packetReceived = FALSE;

		lcdGoTo(2,1);
		if (bookStatus == BOOK_RSP_SUCCESS) {
			lcdFlashPrint(checkedOutStr);
		} else {
			lcdFlashPrint(errorStr);
		}
		break;
	}
}

void bookScan( void ) {
	BYTE *pTag;

	pTag = scanBook();
	zigbeeSendCmd(ZIGBEE_CMD_SCAN_RSP, 0, pTag);
}

static BYTE * scanBook( void ) {
	BYTE *pTagNumber = NULL;

	while (pTagNumber == NULL) {
		if (userCancelRequest) return NULL;
		pTagNumber = rfidscanBook();
		delay_us(500);
	}
	return pTagNumber;
}
