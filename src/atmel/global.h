#ifndef _TYPE_H_
#define _TYPE_H_

#include "pin.h"
#include <inttypes.h>

/* Verbose */
#define DEBUG						0

/* Uart */
#define UART_BAUD_RATE              9600
#define UART_UBRR                   F_CPU/16/UART_BAUD_RATE-1

#define TRUE 	                    1
#define FALSE 	                    0

#ifndef NULL
#define NULL 	                    ((void*)0)
#endif

/* Type Definitions */
typedef unsigned char BYTE;
typedef unsigned char BOOL;
typedef uint64_t RFID;
typedef void (*pFunc)(void);		/* Function Pointer */

/* User Authentication */
#define USER_AUTH_UN_INIT			0x00
#define USER_AUTH_LIBRARIAN			0x01
#define USER_AUTH_STUDENT			0x02

/* Zigbee Protocol Commands */
#define ZIGBEE_START_BIT			0x01
#define ZIGBEE_CMD_BOOKLIST_INIT	0x10
#define ZIGBEE_CMD_SCAN_REQ			0x20
#define ZIGBEE_CMD_SCAN_RSP			0x21
#define ZIGBEE_CMD_MOD_BOOK_REQ		0x30
#define ZIGBEE_CMD_MOD_BOOK_RSP		0x31
#define ZIGBEE_CMD_MISPLACED_CHECK	0x40
#define ZIGBEE_CMD_MISPLACED_END	0x41
#define ZIGBEE_CMD_AUTH				0x50
#define ZIGBEE_CMD_SECURITY_REQ		0x60
#define ZIGBEE_CMD_SECURITY_RSP		0x61

/* Mod Bok Status */
#define BOOK_RSP_SUCCESS			0x01
#define BOOK_RSP_FAIL				0x02

/* Book States */
#define BOOK_STATE_CHECKOUT			0x10
#define BOOK_STATE_CHECKIN			0x20

/* Book Struct Attribute Sizes */
#define ZIGBEE_BOOK_TITLE_SIZE		12		/* Used for Parsing */
#define ZIGBEE_SHELF_TITLE_SIZE		12
#define BOOK_SIZE_TITLE				14		/* Used for initializing array */
#define SHELF_SIZE_TITLE            14		/* Extra 2 bytes for adding "\0" */
#define RFID_TAG_SIZE				8

/* Delay */
#define DELAY_ZIGBEE_SEND			500		/* ms */

struct book;
typedef struct book {
	char title[BOOK_SIZE_TITLE];
	BYTE rfid[RFID_TAG_SIZE];
	BYTE shelfId[SHELF_SIZE_TITLE];
    struct book *pNext;
} book;

/* Global Variables */
extern volatile BOOL userCancelRequest;
extern volatile BYTE user_auth;
extern volatile BOOL packetReceived;
extern volatile BYTE bookStatus;
extern volatile BOOL buttonEnter;
extern volatile BYTE isCheckedOut;

/* Program Memory Str */
extern const char scanEnterStr[];
extern const char exitBackStr[];
extern const char checkInStr[];
extern const char checkOutStr[];
extern const char errorStr[];
extern const char scanningStr[];
extern const char checkedInStr[];
extern const char checkedOutStr[];
extern const char pressEnterStr[];
extern const char toContinueStr[];
extern const char misplacedStr[];
extern const char scanningBooksStr[];
extern const char pressBackStr[];
extern const char toExitStr[];
extern const char toStartStr[];
extern const char scanningBookStr[];

#endif /* _TYPE_H_ */
