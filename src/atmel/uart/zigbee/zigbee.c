#include <avr/io.h>
#include <avr/interrupt.h>
#include <stdlib.h>
#include "zigbee.h"
#include "util.h"
#include "book.h"
#include "lcdMenu.h"

#include "lcd.h"


/* Helper Functions */
static void zigbeeSendRfid( BYTE * const );
static void zigbeeSendByte( const BYTE);
static void zigbeeParseRx( const BYTE, BYTE * const);
static BYTE zigbeePacketSize( const BYTE * const pData );

/* Rx Buffer */
static volatile BYTE ZIGBEE_UART_RxBuf[ZIGBEE_MAX_PACKET_SIZE];
static volatile BYTE ZIGBEE_UART_RxIndex;
/* Tx Buffer */
static volatile BYTE ZIGBEE_UART_TxBuf[ZIGBEE_MAX_PACKET_SIZE];
static volatile BYTE ZIGBEE_UART_TxHead;
static volatile BYTE ZIGBEE_UART_TxTail;
/* Packet Details */
static volatile BYTE command;
static volatile BYTE bookInitNum;

/* Global Variables */
volatile BOOL packetReceived;
static uint16_t user_id;					/* User Card No */


ISR(SW_INTERRUPT_ISR) {
	BYTE *pBuf = NULL;
	BYTE i;

	pBuf = malloc(ZIGBEE_UART_RxIndex ? ZIGBEE_UART_RxIndex : 1);
	for (i = 0; i < ZIGBEE_UART_RxIndex; i++) {
    	*(pBuf+i) = ZIGBEE_UART_RxBuf[i];
	}
	ZIGBEE_UART_RxIndex = 0;

    sei();

	zigbeeParseRx(command, pBuf);
	free(pBuf);
	
	/* reset sw interrupt */
    SW_INTERRUPT_PORT &= ~_BV(SW_INTERRUPT_ZIGBEE);
}

ISR(ZIGBEE_UART_RX_INTERRUPT) {
	static BYTE byteCnt = 0;
	static BYTE packetSize;
	BOOL fillBuf = FALSE;
	BOOL reset = FALSE;
	BYTE data = ZIGBEE_UART_DATA;	

	switch(++byteCnt) {
		case 1:
			/* start of file */
			if ( data != ZIGBEE_START_BIT ) reset = TRUE;
			break;
		case 2:
			/* packet size */
			packetSize = data;
			break;
		case 3:
			/* command */
			command = data;
			break;
		default:
			switch(command) {
				case ZIGBEE_CMD_AUTH:
					switch(byteCnt) {
						case 4:
							user_auth = data;
							break;
						case 5:
							user_id |= data << 8;
							break;
						case 6:
							user_id |= data;
							break;
						default:
							reset = TRUE;
					}
					break;
				case ZIGBEE_CMD_BOOKLIST_INIT:
					switch(byteCnt) {
						case 4:
							user_auth = data;
							break;
						case 5:
							user_id |= data << 8;
							break;
						case 6:
							user_id |= data;												
							break;
						case 7:
							bookInitNum = data;				
							break;
						default:
							fillBuf = TRUE;
							break;
					}
					break;
				case ZIGBEE_CMD_SECURITY_RSP:
					isCheckedOut = data;
					break;
				case ZIGBEE_CMD_MOD_BOOK_RSP:			
					bookStatus = data;
					break;
					/* Fall Through */
				default:
#if DEBUG
					printf("zigbee.c-cmd");
#endif				
					reset = TRUE;
			}
	}

	if (fillBuf) {
		if ( ZIGBEE_UART_RxIndex <= ZIGBEE_MAX_PACKET_SIZE ) {
			ZIGBEE_UART_RxBuf[ZIGBEE_UART_RxIndex++] = data;
		} else {
#if DEBUG
			printf("zigbee.c-ovrflw");
#endif
			reset = TRUE;			
	    }
	}

	if ( byteCnt > 3  && byteCnt > packetSize ) reset = TRUE;
	
    if ( byteCnt == packetSize ) {
		byteCnt = 0;
		SW_INTERRUPT_PORT |= _BV(SW_INTERRUPT_ZIGBEE);
	}

	if ( reset ) {
		byteCnt = 0;
		ZIGBEE_UART_RxIndex = 0;
	}
}

ISR(ZIGBEE_UART_TX_INTERRUPT) {	
    if (ZIGBEE_UART_TxHead != ZIGBEE_UART_TxTail) {
        ZIGBEE_UART_DATA = ZIGBEE_UART_TxBuf[ZIGBEE_UART_TxTail];
        ZIGBEE_UART_TxTail = (ZIGBEE_UART_TxTail + 1) & ZIGBEE_UART_BUFFER_MASK;	
    } else {
        /* tx buffer empty, disable tx interrupt */
        ZIGBEE_UART_CONTROL_B &= ~_BV(ZIGBEE_UART_UDRIE);
    }
}

void zigbeeInit( void ) {
	/* Zigbee UART */
	ZIGBEE_UART_TxHead = 0;
	ZIGBEE_UART_TxTail = 0;
	ZIGBEE_UART_RxIndex = 0;

    ZIGBEE_UBRRH = (BYTE)((UART_UBRR)>>8);
    ZIGBEE_UBRRL = (BYTE) (UART_UBRR) & 0xFF;

	ZIGBEE_UART_CONTROL_A = _BV(RXC0);
	ZIGBEE_UART_CONTROL_B = _BV(RXEN0)|_BV(TXEN0)|_BV(RXCIE0);
	ZIGBEE_UART_CONTROL_C = _BV(URSEL0)|_BV(UCSZ01)|_BV(UCSZ00);

	packetReceived = FALSE;
	user_id = 0;			

    /* sw interrupt */
	MCUCR = _BV(ISC01)|_BV(ISC00);
	GICR = _BV(INT0);
	DDRD |= _BV(SW_INTERRUPT_ZIGBEE);
}

void zigbeeSendFindMisplaced ( const BYTE command, BYTE * const pShelf,  BYTE * const pBook ) {
	BYTE packetSize = 0;

	if (command != ZIGBEE_CMD_MISPLACED_END) {
		if (pShelf == NULL || pBook == NULL) return;
	
		packetSize += zigbeePacketSize(pShelf);
		packetSize += zigbeePacketSize(pBook);
	}	
    packetSize += 3;	/* 3 bytes of header */

	zigbeeSendByte(ZIGBEE_START_BIT);
    zigbeeSendByte(packetSize);
    zigbeeSendByte(command);

	switch(command) {
		case ZIGBEE_CMD_MISPLACED_CHECK:
			zigbeeSendRfid(pBook);
			zigbeeSendRfid(pShelf);
			break;
		case ZIGBEE_CMD_MISPLACED_END:
		default:
			/* Do Nothing */
            break;
	}
}

void zigbeeSendCmd( const BYTE command, const BYTE newState, BYTE * const pData) {
	BYTE packetSize;

	if (userCancelRequest) return;

	delay_ms(DELAY_ZIGBEE_SEND);

	packetSize = zigbeePacketSize(pData);
    packetSize += 3;	/* 3 bytes of header */

	zigbeeSendByte(ZIGBEE_START_BIT);
	
	switch(command) {
	case ZIGBEE_CMD_MOD_BOOK_REQ:
		packetSize += 3;					/* Account for User Id, New State */
        zigbeeSendByte(packetSize);
        zigbeeSendByte(command);
		zigbeeSendByte(user_id>>8&0xff);	/* User Id */
        zigbeeSendByte(user_id&0xff);
		zigbeeSendByte(newState);			/* New State */
		break;
	case ZIGBEE_CMD_SCAN_RSP:
	case ZIGBEE_CMD_SECURITY_REQ:
    	zigbeeSendByte(packetSize);
        zigbeeSendByte(command);
	}

	zigbeeSendRfid(pData);
}

static void zigbeeParseRx( const BYTE command, BYTE * const pBuffer ) {
	switch(command) {
		case ZIGBEE_CMD_BOOKLIST_INIT:
			bookListBuild(bookInitNum, pBuffer);
			break;
		case ZIGBEE_CMD_SCAN_REQ:
			bookScan();
			break;
		case ZIGBEE_CMD_AUTH:
			menuBuild(0);
			break;
		case ZIGBEE_CMD_MOD_BOOK_RSP:
		case ZIGBEE_CMD_SECURITY_RSP:
			packetReceived = TRUE;
			break;
		default:
#if DEBUG
			printf("zigbeeParse-unkwn cmd");
#endif			
			break;
	}
}

static void zigbeeSendRfid( BYTE * const pData ) {
	BYTE i;

	for(i=0; i<RFID_TAG_SIZE; i++)
		zigbeeSendByte(*(pData+i));

}

static void zigbeeSendByte( const BYTE data ) {
    BYTE tmpHead;

    tmpHead  = (ZIGBEE_UART_TxHead + 1) & ZIGBEE_UART_BUFFER_MASK;
	while ( tmpHead == ZIGBEE_UART_TxTail ) {
		; /* wait for free space in buffer */
	}

	ZIGBEE_UART_TxBuf[ZIGBEE_UART_TxHead] = data;
	ZIGBEE_UART_TxHead = tmpHead;

    ZIGBEE_UART_CONTROL_B |= _BV(ZIGBEE_UART_UDRIE);
}


static BYTE zigbeePacketSize( const BYTE * const pData ) {
	return RFID_TAG_SIZE;
}
