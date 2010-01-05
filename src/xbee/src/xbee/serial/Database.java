import java.sql.*;

public interface Database {

	public Connection getConnection() throws ClassNotFoundException,
					InstantiationException, IllegalAccessException, SQLException;
	
	public void closeConnection();
	
}
