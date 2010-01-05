import java.sql.*;

import com.sun.corba.se.spi.orbutil.fsm.State;

public class MySQLDatabase implements Database {

	private static final String DATABASE = "library";
	private static final String USER = "root";
	private static final String PASSWORD = "123456";
	private static final String HOST = "localhost";
	
	Connection conn;
	
	public MySQLDatabase(){
		try{
			conn = getConnection();
		} catch( Exception ex ){ ex.printStackTrace(); }
	}
	
	public Connection getConnection() throws ClassNotFoundException, 
					InstantiationException, IllegalAccessException, SQLException{
		
		String url = "jdbc:mysql://" + HOST + "/" + DATABASE;
		Class.forName("com.mysql.jdbc.Driver");
		Connection conn = DriverManager.getConnection(url, USER, PASSWORD);
		
		return conn;
	}
	
	public void closeConnection() {
		if( conn != null ){
			try{
				conn.close();
			} catch( Exception ex ){ ex.printStackTrace(); }
		}
	}
	
	public byte getUserPriv( String userNum ){
		
		String userType = "";
		try{
			Statement smt = conn.createStatement();
			String sql = "SELECT UserType FROM users WHERE cardNo = '"+ userNum + "'";
			ResultSet results = smt.executeQuery(sql);
			results.next();
			userType = results.getString("UserType");
			smt.close();
			results.close();
		} catch( SQLException ex ){ ex.printStackTrace(); }
		
		if( userType.equals("Admin") || userType.equals("Librarian") ){
			return 0x01;
		}
		else{
			return 0x02;
		}
	}
	
	public String getBookTitle( long rfidNum ){
		
		String returnString = "";
		try{
			Statement smt = conn.createStatement();
			String sql = "SELECT Title FROM books WHERE rfidNo = '"+rfidNum+"'";
			ResultSet results = smt.executeQuery(sql);
			results.next();
			returnString = results.getString("Title");
			smt.close();
			results.close();
		} catch( SQLException ex ){ ex.printStackTrace(); }
		
		return returnString;
	}
	
	public String getShelf( long rfidNum ){
		
		String returnString = "";
		try{
			Statement smt = conn.createStatement();
			String sql = "SELECT shelf FROM books WHERE rfidNo = '"+rfidNum+"'";
			ResultSet results = smt.executeQuery(sql);
			results.next();
			returnString = results.getString("shelf");
			smt.close();
			results.close();
		} catch( SQLException ex ){ ex.printStackTrace(); }
		
		return returnString;
	}
	
	public void modifyBookState( int[] data ) throws DataIncorrectException{
		
		int userID = 0;
		long RFIDnum = 0;
		
		if( data.length != 0x0E ){
			System.err.println("RFID data not of correct length.");
			throw new DataIncorrectException();
		}
		else{
			//process user number
			int[] userIDarray = new int[2];
			for( int i = 0; i < 2; i++ ){
				userIDarray[i] = data[3 + i];
			}
			userID = BufferHelper.bytesToByte(userIDarray);
			
			//process book number
			int[] RFIDnumArray = new int[8];
			for( int i = 0; i < 8; i++ ){
				RFIDnumArray[i] = data[6 + i];
			}
			RFIDnum = BufferHelper.bytesToLong(RFIDnumArray, RFIDnumArray.length);
			
			if( data[5] == 0x10 ){
				//sign book out
				try{
					//Get call number
					Statement smt = conn.createStatement();
					String getCallNoSql = "SELECT CallNo FROM Books WHERE rfidNo = '" + RFIDnum + "'";
					ResultSet results = smt.executeQuery(getCallNoSql);
					results.next();
					String callNo = results.getString("callNo");
					smt.close();
					results.close();
					
					//Get date
					Statement getDate = conn.createStatement();
					String getDateSql = "SELECT DATE_FORMAT( DATE_ADD( CURDATE(), INTERVAL 14 DAY ), '%Y-%m-%d' )";
					ResultSet dateSet = getDate.executeQuery(getDateSql);
					dateSet.next();
					Date dueDate = dateSet.getDate(1);
					String dueDateString = dueDate.toString();
					dateSet.close();
					getDate.close();
					
					Statement submit = conn.createStatement();
					String updateLoan = "INSERT INTO onLoan (DueDate, CallNo, UserId) VALUES ('"+dueDateString+"', '"+callNo+"', '"+userID+"')";
					int result = submit.executeUpdate(updateLoan);
					if(result != 1){ System.err.println("Loan Update Unsuccessful"); }
					submit.close();				
					
					PHPHandler myPHP = new PHPHandler();
					myPHP.sendCheckInOutResponse((byte) 0x01);
					
				} catch( Exception ex ){ 
					PHPHandler myPHP = new PHPHandler();
					myPHP.sendCheckInOutResponse((byte) 0x02);
				}
			}
			else if( data[5] == 0x20 ){
				//sign a book in
				try{
					//Get call number
					Statement smt = conn.createStatement();
					String getCallNoSql = "SELECT CallNo FROM Books WHERE rfidNo = '" + RFIDnum + "'";
					ResultSet results = smt.executeQuery(getCallNoSql);
					results.next();
					String callNo = results.getString("callNo");
					smt.close();
					results.close();
					
					//sign book back in
					Statement signIn = conn.createStatement();
					String signInSql = "DELETE FROM onLoan WHERE callNo = '" + callNo + "'";
					int result = signIn.executeUpdate(signInSql);
					if(result != 1){ 
						PHPHandler myPHP = new PHPHandler(); 
						myPHP.sendCheckInOutResponse((byte) 0x02); 
					}
					else{
						PHPHandler myPHP = new PHPHandler();
						myPHP.sendCheckInOutResponse((byte) 0x01);
					}
					signIn.close();
					
				} catch( Exception ex ){ 
					PHPHandler myPHP = new PHPHandler();
					myPHP.sendCheckInOutResponse((byte) 0x02);
				}
			}
		}
	}
	
	public void addToMisplacedTable(int[] data) throws DataIncorrectException{
		if( data.length != 0x13 ){
			System.err.println("RFID data not of correct length.");
			throw new DataIncorrectException();
		}
		else{
			int[] temp = new int[8];
			for( int i = 0; i < 8; i++ ){
				temp[i] = data[i+3];
			}
			long bookID = BufferHelper.bytesToLong(temp, temp.length);
			
			for( int i = 0; i < 8; i++ ){
				temp[i] = data[i+11];
			}
			long shelfID = BufferHelper.bytesToLong(temp, temp.length);
			
			try{
				Statement smt1 = conn.createStatement();
				String sql1 = "INSERT INTO misplacedchecklist (ShelfID, BookID) VALUES ('"+ shelfID +"', '"+ bookID +"')";
				smt1.executeUpdate(sql1);
			} catch( SQLException ex ){ System.out.println("Book already checked!"); }
		}
	}
	
	public byte securityCheck(int[] data) throws DataIncorrectException{
		if( data.length != 0x0B ){
			System.err.println("RFID data not of correct length.");
			throw new DataIncorrectException();
		}
		else{
			String state = "";
			
			int[] temp = new int[8];
			for( int i = 0; i < 8; i++ ){
				temp[i] = data[i+3];
			}
			long bookID = BufferHelper.bytesToLong(temp, temp.length);
			
			try{
				Statement smt = conn.createStatement();
				String sql = "SELECT State FROM Books WHERE rfidNo = '"+ bookID +"'";
				ResultSet results = smt.executeQuery(sql);
				
				results.next();
				state = results.getString("State");
				
			} catch( SQLException ex ){ ex.printStackTrace(); }
			
			if( state.equals("On Loan") ){
				return 0x10;
			}
			else{
				return 0x20;
			}
		}
	}
	
	public void checkMisplacedShelves(){
		
		try{
			Statement booksToCheck = conn.createStatement();
			String sql = "SELECT * FROM misplacedCheckList";
			ResultSet misplacedResults = booksToCheck.executeQuery(sql);
			
			while(misplacedResults.next()){
				Statement bookDetail = conn.createStatement();
				String  newSql = "SELECT * FROM Books WHERE rfidNo = '" + misplacedResults.getLong("BookID") + "'";
				ResultSet book = bookDetail.executeQuery(newSql);
				book.next();
				
				Statement shelfDesc = conn.createStatement();
				String shelfSql = "SELECT description FROM shelves WHERE ShelfID = '" + misplacedResults.getString("ShelfID") + "'";
				ResultSet shelfDescResult = shelfDesc.executeQuery(shelfSql);
				shelfDescResult.next();
				
				if( !book.getString("shelf").equals(shelfDescResult.getString("description")) ){
					
					Statement addToMisplaced = conn.createStatement();
					String add = "INSERT INTO misplaced (CallNo, shelf, foundShelf) VALUES ('" +
									book.getString("CallNo") + "', '"  + book.getString("shelf") + "', '" + shelfDescResult.getString("description") + "')";
					
					addToMisplaced.executeUpdate(add);
					addToMisplaced.close();
					
					Statement removeFromCheckList = conn.createStatement();
					String remove = "DELETE FROM misplacedCheckList WHERE BookID = '"+ misplacedResults.getString("BookID") + "'";
					removeFromCheckList.executeUpdate(remove);
					
					removeFromCheckList.close();
					
				}
				
				book.close();
				shelfDescResult.close();
				shelfDesc.close();
				
			}
			
		} catch( Exception ex ){ ex.printStackTrace(); }
		
		
	}
	
	public void clearMisplacedCheckList(){
		
		try{
			Statement smt = conn.createStatement();
			smt.executeUpdate("DELETE FROM misplacedCheckList");
			smt.close();
		}catch( Exception ex ){ ex.printStackTrace(); }
		
	}

}
