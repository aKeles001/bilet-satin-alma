PRAGMA foreign_keys = ON;


-- USERS

CREATE TABLE User (
    id TEXT PRIMARY KEY,                         -- UUID
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    role TEXT CHECK(role IN ('user', 'company', 'admin')) NOT NULL DEFAULT 'user',
    password TEXT NOT NULL,
    company_id TEXT,                              -- Nullable FK (only for company users)
    balance REAL DEFAULT 800,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
);


-- BUS COMPANIES

CREATE TABLE Bus_Company (
    id TEXT PRIMARY KEY,                          -- UUID
    name TEXT UNIQUE NOT NULL,
    logo_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- TRIPS (Seferler)

CREATE TABLE Trips (
    id TEXT PRIMARY KEY,                          -- UUID
    company_id TEXT NOT NULL,
    destination_city TEXT NOT NULL,
    arrival_time DATETIME NOT NULL,
    departure_time DATETIME NOT NULL,
    departure_city TEXT NOT NULL,
    price REAL NOT NULL,
    capacity INTEGER NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
);


-- TICKETS

CREATE TABLE Tickets (
    id TEXT PRIMARY KEY,                          -- UUID
    trip_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    status TEXT CHECK(status IN ('active', 'cancelled', 'expired')) DEFAULT 'active',
    total_price REAL NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES Trips(id),
    FOREIGN KEY (user_id) REFERENCES User(id)
);


-- BOOKED SEATS

CREATE TABLE Booked_Seats (
    id TEXT PRIMARY KEY,                          -- UUID
    ticket_id TEXT NOT NULL,
    seat_number INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES Tickets(id)
);


-- COUPONS

CREATE TABLE Coupons (
    id TEXT PRIMARY KEY,                          -- UUID
    code TEXT UNIQUE NOT NULL,
    discount REAL NOT NULL,
    company_id TEXT,                              -- Nullable FK (if null, coupon is admin)
    usage_limit INTEGER NOT NULL,
    expire_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
);


-- USER_COUPONS

CREATE TABLE User_Coupons (
    id TEXT PRIMARY KEY,                          -- UUID
    coupon_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES Coupons(id),
    FOREIGN KEY (user_id) REFERENCES User(id)
);
