#include <avr/io.h>
#include <avr/interrupt.h>
#include "rfid.h"
#include "lcd.h"
#include "global.h"

#define RFID_REQUEST_PACKET_SIZE 9
static BYTE requestPacket[] = {0x01, 0x09, 0x00, 0x03, 0x01, 0x41, 0x0A, 0x41, 0xBE};


/* RFID RX, TX Buffer */
static volatile BYTE RFID_UART_RxBuf[MAX_RFID_PACKET_SIZE];
static volatile BYTE RFID_UART_RxIndex;
static volatile BYTE RFID_UART_TxBuf[MAX_RFID_PACKET_SIZE];
static volatile BYTE RFID_UART_TxHead;
static volatile BYTE RFID_UART_TxTail;

static volatile BOOL rfidResponseReceived;
static volatile BOOL errorReceived;
static volatile BYTE recievedTag[RFID_TAG_SIZE-1];

static void rfidSendByte(BYTE);
static void rfidParseRx( BYTE );


void rfidInit( void ) {
	RFID_UART_TxHead = 0;
	RFID_UART_TxTail = 0;
	RFID_UART_RxIndex = 0;

    UBRR1H = (BYTE)((UART_UBRR)>>8);
    UBRR1L = (BYTE) (UART_UBRR) & 0xFF;

	RFID_UART_CONTROL_B = _BV(RXEN1)|_BV(TXEN1)|_BV(RXCIE1);
	RFID_UART_CONTROL_C = _BV(URSEL1)|_BV(UCSZ11)|_BV(UCSZ10);
}

static void rfidSendByte(BYTE data) {
    BYTE tmpHead;

    tmpHead  = (RFID_UART_TxHead + 1) & RFID_UART_TX_BUFFER_MASK;

	while ( tmpHead == RFID_UART_TxTail ) {
		;/* wait for free space in buffer */
	}
	RFID_UART_TxBuf[tmpHead] = data;
	RFID_UART_TxHead = tmpHead;

	RFID_UART_CONTROL_B |= _BV(RFID_UART_UDRIE);
}


ISR (RFID_UART_RX_INTERRUPT) {
	static BYTE byteCnt = 0;
	static BYTE packetSize;
	BYTE data = RFID_UART_DATA;
	BYTE reset = FALSE;

	switch(++byteCnt) {
	case 1:
		/* start of file */
		if ( data != 0x01 ) errorReceived = TRUE;
		break;
	case 2:
		/* length LSB */
		packetSize = data;
		break;
	case 3:
		/* length MSB */
		packetSize |= data << 8;
		break;
	case 4:
		/* Device ID */
		if ( data != 0x03 ) errorReceived = TRUE;
		break;
	case 7:
		/* Status */
		if (data != 0x00) errorReceived = TRUE;
		break;
	default:
        break;
		
	}
	if (RFID_UART_RxIndex == MAX_RFID_PACKET_SIZE) {			
		errorReceived = TRUE;
	} else {
		RFID_UART_RxBuf[RFID_UART_RxIndex++] = data;
	}	

	if ( packetSize == byteCnt ) {
		rfidParseRx(packetSize);
		reset = TRUE;
	}

	if ( errorReceived || reset ) {
		byteCnt = 0;
		RFID_UART_RxIndex = 0;		
	}
}

ISR(RFID_UART_TX_INTERRUPT) {
    BYTE tmptail;
    if ( RFID_UART_TxHead != RFID_UART_TxTail) {
        tmptail = (RFID_UART_TxTail + 1) & RFID_UART_TX_BUFFER_MASK;
        RFID_UART_TxTail = tmptail;
		/* retreive one byte from buffer and write it to UART */
        RFID_UART_DATA = RFID_UART_TxBuf[tmptail];
    } else {
        /* tx buffer empty, disable UDRE interrupt */
        RFID_UART_CONTROL_B &= ~_BV(RFID_UART_UDRIE);
    }
}

BYTE* rfidscanBook( void ) {
	BYTE i;
	errorReceived = FALSE;
	rfidResponseReceived = FALSE;

	/* Send request packet */
	for (i = 0; i < RFID_REQUEST_PACKET_SIZE; i++) {
		rfidSendByte(requestPacket[i]);
	}	
	/* Wait until response is received*/
	while(!rfidResponseReceived && !errorReceived) {
		if (userCancelRequest) break;
	}

	/* If there was an error in reading the tag, need to reset error
	   flag to FALSE and return NULL instead of the tag*/
	if (errorReceived) {
		return NULL;
	} else {
		return recievedTag;
	}
}

static void rfidParseRx( BYTE size) {
	BYTE i;
	BYTE j = 0;

	/* check if error is received instead of the tag */
	if (errorReceived) {
		rfidResponseReceived = FALSE;
	} else {
		/* Parse the tags from the response packet */
		for (i = 10; i < 18; i++) {
			recievedTag[j++] = RFID_UART_RxBuf[i];
#if DEBUG
			printf("%02x ", RFID_UART_RxBuf[i]);
#endif

		}		
		/* set response successfull received flag */
		rfidResponseReceived = TRUE;
	}
}

BOOL rfidFindBook( BYTE* tagId ) {
	BOOL tagFound = FALSE;
	BYTE l;
	BYTE* tagRecieved = NULL;

	/* Scan for tag */
	tagRecieved = rfidscanBook();
	if (tagRecieved != NULL) {
		for (l = 0; l < 8; l++) {
			if (tagRecieved[l] != tagId[l]) {
				tagFound = FALSE;
				break;
			} else {
				tagFound = TRUE;
			}
		}
	}
	return tagFound;
 	
}
